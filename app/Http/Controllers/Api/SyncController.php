<?php
// app/Http/Controllers/Api/SyncController.php - FIXED WITH SCHOOL FILTERING

namespace App\Http\Controllers\Api;

use App\Models\{User, Course, Fleet, Schedule, Invoice, Payment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncController extends BaseController
{
    public function download(Request $request)
    {
        try {
            // Get the authenticated user and their school
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;

            Log::info('Sync download request', [
                'user_id' => $currentUser->id,
                'user_email' => $currentUser->email,
                'school_id' => $schoolId
            ]);

            if (!$schoolId) {
                return $this->sendError('User does not belong to any school.', [], 400);
            }

            $lastSync = $request->header('Last-Sync')
                ? Carbon::parse($request->header('Last-Sync'))
                : Carbon::now()->subYears(10); // Get all data if no last sync

            // ✅ FILTER ALL DATA BY SCHOOL_ID
            $data = [
                'users' => User::where('school_id', $schoolId)
                    ->where('updated_at', '>', $lastSync)
                    ->get(),
                    
                'courses' => Course::where('school_id', $schoolId)
                    ->where('updated_at', '>', $lastSync)
                    ->get(),
                    
                'fleet' => Fleet::where('school_id', $schoolId)
                    ->where('updated_at', '>', $lastSync)
                    ->get(),
                    
                'schedules' => Schedule::with(['student', 'instructor', 'course', 'car'])
                    ->where('school_id', $schoolId)
                    ->where('updated_at', '>', $lastSync)
                    ->get(),
                    
                'invoices' => Invoice::with(['student', 'course', 'payments'])
                    ->where('school_id', $schoolId)
                    ->where('updated_at', '>', $lastSync)
                    ->get(),
                    
                'payments' => Payment::with(['invoice', 'user'])
                    ->whereHas('user', function($query) use ($schoolId) {
                        $query->where('school_id', $schoolId);
                    })
                    ->where('updated_at', '>', $lastSync)
                    ->get(),
                    
                'sync_timestamp' => now()->toISOString(),
            ];

            Log::info('Sync download response', [
                'school_id' => $schoolId,
                'users_count' => count($data['users']),
                'courses_count' => count($data['courses']),
                'fleet_count' => count($data['fleet']),
                'schedules_count' => count($data['schedules']),
                'invoices_count' => count($data['invoices']),
                'payments_count' => count($data['payments']),
            ]);

            return $this->sendResponse($data, 'Data synchronized successfully.');

        } catch (\Exception $e) {
            Log::error('Sync download failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('Sync failed.', ['error' => $e->getMessage()], 500);
        }
    }


/**
 * Enhanced processIndividualItem with better error handling
 */
private function processIndividualItem($type, $data, $operation)
{
    try {
        Log::debug("Processing {$type} with operation {$operation}", [
            'data_keys' => array_keys($data),
            'has_id' => isset($data['id'])
        ]);

        $result = null;
        
        switch ($type) {
            case 'users':
                $result = $this->upsertUser($data, $operation);
                break;
            case 'courses':
                $result = $this->upsertCourse($data, $operation);
                break;
            case 'fleet':
                $result = $this->upsertFleet($data, $operation);
                break;
            case 'schedules':
                $result = $this->upsertSchedule($data, $operation);
                break;
            case 'invoices':
                $result = $this->upsertInvoice($data, $operation);
                break;
            case 'payments':
                $result = $this->upsertPayment($data, $operation);
                break;
            default:
                throw new \Exception("Unknown data type: {$type}");
        }

        // ✅ FIX: Ensure consistent return format
        if (is_array($result)) {
            return $result;
        } else {
            // Legacy methods that return boolean
            return [
                'success' => $result === true,
                'message' => $result ? 'Processed successfully' : 'Processing failed'
            ];
        }

    } catch (\Exception $e) {
        Log::error("Error processing {$type} item", [
            'error' => $e->getMessage(),
            'data' => $data,
            'operation' => $operation,
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}



public function upload(Request $request)
{
    DB::beginTransaction();

    try {
        $currentUser = auth()->user();
        $schoolId = $currentUser->school_id;
        $changes = $request->get('changes', []);

        if (!$schoolId || empty($changes)) {
            // ... existing validation code
        }

        $results = [];
        $allErrors = [];
        $totalUploaded = 0;
        $totalProcessed = 0;
        
        // ✅ NEW: Track ID mappings for relationships
        $idMappings = [
            'users' => [],     // old_id => new_id
            'courses' => [],   // old_id => new_id
            'fleet' => [],     // old_id => new_id
            'invoices' => [],  // old_id => new_id
        ];

        // ✅ CRITICAL: Process in dependency order to build ID mappings
        $processingOrder = ['users', 'courses', 'fleet', 'invoices', 'payments', 'schedules'];
        
        // Group changes by type for ordered processing
        $changesByType = [];
        foreach ($changes as $change) {
            $type = $change['table'];
            if (!isset($changesByType[$type])) {
                $changesByType[$type] = [];
            }
            $changesByType[$type][] = $change;
        }

        // Process each type in dependency order
        foreach ($processingOrder as $type) {
            if (!isset($changesByType[$type])) continue;
            
            foreach ($changesByType[$type] as $change) {
                try {
                    $totalProcessed++;
                    $data = $change['data'];
                    $operation = $change['operation'] ?? 'upsert';
                    $localId = $data['id'] ?? null;
                    
                    // ✅ APPLY ID MAPPINGS for foreign keys
                    if ($type === 'invoices') {
                        // Map student ID
                        if (isset($data['student']) && isset($idMappings['users'][$data['student']])) {
                            $data['student'] = $idMappings['users'][$data['student']];
                            Log::info("Mapped invoice student ID: {$change['data']['student']} -> {$data['student']}");
                        }
                        
                        // Map course ID  
                        if (isset($data['course']) && isset($idMappings['courses'][$data['course']])) {
                            $data['course'] = $idMappings['courses'][$data['course']];
                            Log::info("Mapped invoice course ID: {$change['data']['course']} -> {$data['course']}");
                        }
                    }
                    
                    if ($type === 'schedules') {
                        // Map student, instructor, course, and vehicle IDs
                        if (isset($data['student']) && isset($idMappings['users'][$data['student']])) {
                            $data['student'] = $idMappings['users'][$data['student']];
                        }
                        if (isset($data['instructor']) && isset($idMappings['users'][$data['instructor']])) {
                            $data['instructor'] = $idMappings['users'][$data['instructor']];
                        }
                        if (isset($data['course']) && isset($idMappings['courses'][$data['course']])) {
                            $data['course'] = $idMappings['courses'][$data['course']];
                        }
                        if (isset($data['car']) && isset($idMappings['fleet'][$data['car']])) {
                            $data['car'] = $idMappings['fleet'][$data['car']];
                        }
                    }

                    // Process the item
                    $data['school_id'] = $schoolId;
                    $result = $this->processIndividualItem($type, $data, $operation);
                    
                    if ($result['success']) {
                        $totalUploaded++;
                        $serverId = $result['id'] ?? null;
                        
                        // ✅ STORE ID MAPPING for future references
                        if ($localId && $serverId && $localId != $serverId) {
                            $idMappings[$type][$localId] = $serverId;
                            Log::info("ID mapping: {$type} {$localId} -> {$serverId}");
                        }
                        
                        $results[] = [
                            'type' => $type,
                            'local_id' => $localId,
                            'server_id' => $serverId,
                            'status' => 'success'
                        ];
                    } else {
                        $allErrors[] = [
                            'type' => $type,
                            'item_id' => $localId,
                            'error' => $result['error'] ?? 'Processing failed',
                            'item' => ['data' => $data]
                        ];
                    }
                    
                } catch (\Exception $e) {
                    Log::error("Error processing individual change", [
                        'error' => $e->getMessage(),
                        'change' => $change,
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    $allErrors[] = [
                        'error' => $e->getMessage(),
                        'item' => ['data' => $change['data'] ?? $change],
                        'type' => $change['table'] ?? 'unknown'
                    ];
                }
            }
        }

        // Determine success status
        $hasErrors = !empty($allErrors);
        $hasSuccesses = $totalUploaded > 0;
        
        if ($hasSuccesses && !$hasErrors) {
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Upload completed successfully.',
                'data' => [
                    'uploaded' => $totalUploaded,
                    'errors' => [],
                    'successful_items' => $results,
                    'id_mappings' => $idMappings, // ✅ RETURN ID MAPPINGS
                    'timestamp' => now()->toISOString(),
                ]
            ], 200);
            
        } elseif ($hasSuccesses && $hasErrors) {
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Upload partially completed.',
                'data' => [
                    'uploaded' => $totalUploaded,
                    'errors' => $allErrors,
                    'successful_items' => $results,
                    'id_mappings' => $idMappings, // ✅ RETURN ID MAPPINGS
                    'partial' => true,
                    'timestamp' => now()->toISOString(),
                ]
            ], 200);
            
        } else {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Upload failed completely.',
                'data' => [
                    'uploaded' => 0,
                    'errors' => $allErrors,
                    'successful_items' => [],
                    'timestamp' => now()->toISOString(),
                ]
            ], 422);
        }

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Sync upload failed completely', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Upload failed: ' . $e->getMessage(),
            'data' => [
                'uploaded' => 0,
                'errors' => [['error' => $e->getMessage()]],
                'successful_items' => [],
                'timestamp' => now()->toISOString(),
            ]
        ], 500);
    }
}

// COMPLETE REPLACEMENT for your processDataType method
private function processDataType($type, $data, $schoolId)
{
    $uploaded = 0;
    $errors = [];
    $successfulItems = []; // ✅ INITIALIZE THIS ARRAY

    foreach ($data as $index => $item) {
        try {
            Log::info("Processing {$type} item #{$index}", ['item' => $item]);

            // ✅ FIXED: Better null handling
            if (!is_array($item)) {
                throw new \Exception("Invalid item format: expected array, got " . gettype($item));
            }

            $actualData = [];
            $operation = 'create';

            // Handle different data formats
            if (isset($item['data']) && is_array($item['data'])) {
                $actualData = $item['data'];
            } else {
                $actualData = $item;
            }

            if (isset($item['operation'])) {
                $operation = $item['operation'];
            }

            // Validate operation
            if (!in_array($operation, ['create', 'update', 'delete'])) {
                throw new \Exception("Invalid operation: {$operation}");
            }

            // ✅ ENSURE SCHOOL_ID IS SET FOR ALL DATA
            $actualData['school_id'] = $schoolId;

            Log::info("Extracted data for {$type}", [
                'operation' => $operation, 
                'school_id' => $schoolId,
                'item_id' => $actualData['id'] ?? 'new',
                'data_keys' => array_keys($actualData)
            ]);

            // ✅ FIXED: Process each type with proper error handling
            switch ($type) {
                case 'users':
                    $this->upsertUser($actualData, $operation);
                    break;
                case 'courses':
                    $this->upsertCourse($actualData, $operation);
                    break;
                case 'fleet':
                    $this->upsertFleet($actualData, $operation);
                    break;
                case 'schedules':
                    $this->upsertSchedule($actualData, $operation);
                    break;
                case 'invoices':
                    $this->upsertInvoice($actualData, $operation);
                    break;
                case 'payments':
                    $this->upsertPayment($actualData, $operation);
                    break;
                default:
                    throw new \Exception("Unknown data type: {$type}");
            }

            $uploaded++;
            
            // ✅ TRACK SUCCESSFUL ITEMS
            $successfulItems[] = [
                'id' => $actualData['id'] ?? null,
                'operation' => $operation,
                'type' => $type,
                'index' => $index
            ];

            Log::info("{$type} item #{$index} processed successfully", [
                'item_id' => $actualData['id'] ?? 'new',
                'operation' => $operation
            ]);

        } catch (\Exception $e) {
            $errorDetails = [
                'item_index' => $index,
                'item_data' => $actualData ?? $item ?? null,
                'error' => $e->getMessage(),
                'operation' => $operation ?? 'unknown',
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ];
            
            $errors[] = $errorDetails;
            
            Log::error("Failed to process {$type} item #{$index}", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'item' => $item ?? null,
                'actual_data' => $actualData ?? null
            ]);
        }
    }

    Log::info("Completed processing {$type}", [
        'uploaded' => $uploaded,
        'errors' => count($errors),
        'successful_items' => count($successfulItems),
        'total_processed' => count($data)
    ]);

    return [
        'uploaded' => $uploaded,
        'errors' => $errors,
        'successful_items' => $successfulItems, // ✅ ALWAYS RETURN THIS
    ];
}

/**
 * Check if errors contain critical issues that should cause rollback
 */
private function checkForCriticalErrors($errors)
{
    $criticalErrorPatterns = [
        'Foreign key constraint',
        'duplicate key value',
        'violates unique constraint',
        'database connection',
        'syntax error',
        'permission denied'
    ];
    
    foreach ($errors as $errorGroup) {
        if (is_array($errorGroup)) {
            foreach ($errorGroup as $error) {
                $errorMessage = strtolower($error['error'] ?? '');
                
                foreach ($criticalErrorPatterns as $pattern) {
                    if (strpos($errorMessage, strtolower($pattern)) !== false) {
                        return true;
                    }
                }
            }
        }
    }
    
    return false;
}

/**
 * Check if array is associative (Map format)
 */
private function isAssociativeArray($array)
{
    if (!is_array($array) || empty($array)) {
        return false;
    }
    
    return array_keys($array) !== range(0, count($array) - 1);
}

/**
 * Convert Map format to List format
 */
private function convertMapFormatToListFormat($mapFormat)
{
    $listFormat = [];
    
    foreach ($mapFormat as $table => $items) {
        if (is_array($items)) {
            foreach ($items as $item) {
                $listFormat[] = [
                    'table' => $table,
                    'operation' => $item['operation'] ?? 'upsert',
                    'data' => $item['data'] ?? $item,
                    'id' => isset($item['data']) ? ($item['data']['id'] ?? null) : ($item['id'] ?? null)
                ];
            }
        }
    }
    
    return $listFormat;
}



private function upsertUser($data, $operation)
{
    try {
        if ($operation === 'delete') {
            User::where('id', $data['id'] ?? null)
                 ->where('school_id', $data['school_id'] ?? null)
                 ->delete();
            return ['success' => true, 'message' => 'User deleted'];
        }

        // ✅ FIX 1: Validate required fields with proper null checks
        if (empty($data['email'])) {
            throw new \Exception('Email is required for user operations');
        }

        // ✅ FIX 2: Handle all data fields safely
        $userData = [
            'school_id' => $data['school_id'],
            'fname' => $data['fname'] ?? '',
            'lname' => $data['lname'] ?? '',
            'email' => $data['email'],
            'role' => $data['role'] ?? 'student',
            'phone' => $data['phone'] ?? '',
            'status' => $data['status'] ?? 'active',
            'date_of_birth' => $data['date_of_birth'] ?? '2000-01-01',
            'gender' => $data['gender'] ?? 'other',
            'address' => $data['address'] ?? '',
            'idnumber' => $data['idnumber'] ?? null,
        ];

        // ✅ FIX 3: Handle password safely - don't sync existing passwords for security
        if (isset($data['password']) && !empty($data['password']) && $operation === 'create') {
            // Only set password for new users
            $userData['password'] = bcrypt($data['password']);
        }

        Log::info('Processing user', [
            'operation' => $operation,
            'user_id' => $data['id'] ?? 'new',
            'email' => $data['email'],
            'user_data_keys' => array_keys($userData)
        ]);

        $user = User::updateOrCreate(
            [
                'id' => $data['id'] ?? null,
                'school_id' => $data['school_id'],
            ],
            $userData
        );

        Log::info('User processed successfully', [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role
        ]);

        return ['success' => true, 'id' => $user->id, 'message' => 'User processed'];

    } catch (\Exception $e) {
        Log::error('Error processing user', [
            'error' => $e->getMessage(),
            'data' => $data,
            'operation' => $operation,
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false, 
            'error' => $e->getMessage(),
            'debug_data' => $data
        ];
    }
}


// ✅ EXAMPLE upsert method (implement similar for other types)
private function upsertCourse($data, $operation)
{
    try {
        // Validate required fields
        if (empty($data['name'])) {
            return ['success' => false, 'error' => 'Course name is required'];
        }

        $course = Course::updateOrCreate(
            [
                'id' => $data['id'] ?? null,
                'school_id' => $data['school_id']
            ],
            [
                'name' => $data['name'],
                'price' => $data['price'] ?? 0,
                'status' => $data['status'] ?? 'active',
                'school_id' => $data['school_id'],
            ]
        );

        Log::info("Course processed successfully", [
            'id' => $course->id,
            'name' => $course->name,
            'operation' => $operation
        ]);

        return ['success' => true, 'id' => $course->id];
        
    } catch (\Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

private function upsertFleet($data, $operation)
{
    try {
        if ($operation === 'delete') {
            Fleet::where('id', $data['id'] ?? null)
                 ->where('school_id', $data['school_id'] ?? null)
                 ->delete();
            return ['success' => true, 'message' => 'Fleet item deleted'];
        }

        // ✅ FIX 1: Validate required fields with null checks
        if (empty($data['make'])) {
            throw new \Exception('Make is required for fleet operations');
        }
        
        if (empty($data['model'])) {
            throw new \Exception('Model is required for fleet operations');
        }

        // ✅ FIX 2: Handle all possible field names and null values safely
        $fleetData = [
            'make' => $data['make'],
            'model' => $data['model'],
            'modelyear' => $data['modelyear'] ?? $data['model_year'] ?? date('Y'),
            'carplate' => $data['carplate'] ?? $data['carPlate'] ?? $data['car_plate'] ?? '',
            'status' => $data['status'] ?? 'available',
            'school_id' => $data['school_id'],
        ];

        // ✅ FIX 3: Handle instructor field properly (this might be the null access issue)
        if (isset($data['instructor']) && !empty($data['instructor'])) {
            // If instructor is provided and not empty
            $fleetData['instructor'] = is_numeric($data['instructor']) ? (int)$data['instructor'] : null;
        } elseif (isset($data['assigned_instructor_id'])) {
            // Alternative field name
            $fleetData['instructor'] = is_numeric($data['assigned_instructor_id']) ? (int)$data['assigned_instructor_id'] : null;
        } elseif (isset($data['instructor_id'])) {
            // Another alternative field name
            $fleetData['instructor'] = is_numeric($data['instructor_id']) ? (int)$data['instructor_id'] : null;
        } else {
            // No instructor assigned
            $fleetData['instructor'] = null;
        }

        // ✅ FIX 4: Add additional fields if they exist
        if (isset($data['notes'])) {
            $fleetData['notes'] = $data['notes'];
        }

        Log::info('Processing fleet item', [
            'original_data' => $data,
            'processed_data' => $fleetData,
            'operation' => $operation
        ]);

        // ✅ FIX 5: Use updateOrCreate with proper conditions
        $fleet = Fleet::updateOrCreate(
            [
                'id' => $data['id'] ?? null,
                'school_id' => $data['school_id'],
            ],
            $fleetData
        );

        Log::info('Fleet item processed successfully', [
            'id' => $fleet->id,
            'make' => $fleet->make,
            'model' => $fleet->model,
            'carplate' => $fleet->carplate
        ]);

        return ['success' => true, 'id' => $fleet->id, 'message' => 'Fleet item processed'];

    } catch (\Exception $e) {
        Log::error('Error processing fleet item', [
            'error' => $e->getMessage(),
            'data' => $data,
            'operation' => $operation,
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false, 
            'error' => $e->getMessage(),
            'debug_data' => $data
        ];
    }
}


private function upsertSchedule($data, $operation)
{
    if ($operation === 'delete') {
        Schedule::where('id', $data['id'])->where('school_id', $data['school_id'])->delete();
        return;
    }

    // ✅ FIXED: Check required fields
    if (!isset($data['start']) || !isset($data['end'])) {
        throw new \Exception('Start and end times are required for schedule operations');
    }

    Schedule::updateOrCreate(
        ['id' => $data['id']],
        [
            'student' => $data['student_id'] ?? $data['student'] ?? null,
            'instructor' => $data['instructor_id'] ?? $data['instructor'] ?? null,
            'course' => $data['course_id'] ?? $data['course'] ?? null,
            'car' => $data['vehicle_id'] ?? $data['car'] ?? null,
            'is_recurring' => $data['is_recurring'] ?? 0,
            'recurring_pattern' => $data['recurrence_pattern'] ?? null,  // ✅ FIXED: Match Flutter field name
            'recurring_end_date' => $data['recurrence_end_date'] ?? null,  // ✅ FIXED: Match Flutter field name
            'attended' => $data['attended'] ?? 0,
            'lessons_deducted' => $data['lessons_deducted'] ?? 0,
            'lessons_completed' => $data['lessons_completed'] ?? 0,
            'start' => $data['start'],
            'end' => $data['end'],
            'status' => $data['status'] ?? 'scheduled',
            'class_type' => $data['class_type'] ?? 'Practical',
            'notes' => $data['notes'] ?? '',
            'school_id' => $data['school_id'],
        ]
    );
}

/**
 * ✅ FIXED: upsertInvoice method with proper field handling
 */
private function upsertInvoice($data, $operation)
{
    try {
        if ($operation === 'delete') {
            Invoice::where('id', $data['id'] ?? null)
                   ->where('school_id', $data['school_id'] ?? null)
                   ->delete();
            return ['success' => true, 'message' => 'Invoice deleted'];
        }

        // ✅ FIX 1: Handle the field name differences between client and server
        $studentId = $data['student'] ?? $data['student_id'] ?? null;
        $courseId = $data['course'] ?? $data['course_id'] ?? null;
        
        // ✅ FIX 2: Validate required fields
        if (empty($studentId)) {
            throw new \Exception('Student ID is required for invoice operations');
        }

        if (empty($data['total_amount']) && empty($data['amount'])) {
            throw new \Exception('Total amount is required for invoice operations');
        }

        // ✅ FIX 3: Verify that referenced records exist
        if ($studentId && !User::where('id', $studentId)->where('school_id', $data['school_id'])->exists()) {
            throw new \Exception("Student with ID {$studentId} not found in school {$data['school_id']}");
        }

        if ($courseId && !Course::where('id', $courseId)->where('school_id', $data['school_id'])->exists()) {
            throw new \Exception("Course with ID {$courseId} not found in school {$data['school_id']}");
        }

        // ✅ FIX 4: Build invoice data with proper field mapping
        $invoiceData = [
            'school_id' => $data['school_id'],
            'student' => $studentId,  // Your table uses 'student' not 'student_id'
            'course' => $courseId,    // Your table uses 'course' not 'course_id'
            'invoice_number' => $data['invoice_number'] ?? 'INV-' . time(),
            'lessons' => $data['lessons'] ?? 1,
            'price_per_lesson' => $data['price_per_lesson'] ?? 0,
            'total_amount' => $data['total_amount'] ?? $data['amount'] ?? 0,
            'amountpaid' => $data['amountpaid'] ?? $data['paid_amount'] ?? 0,
            'status' => $data['status'] ?? 'unpaid',
            'due_date' => $data['due_date'] ?? now()->addDays(30),
        ];

        // ✅ FIX 5: Handle optional fields
        if (isset($data['notes'])) {
            $invoiceData['notes'] = $data['notes'];
        }

        Log::info('Processing invoice', [
            'operation' => $operation,
            'invoiceId' => $data['id'] ?? 'new',
            'student' => $studentId,
            'course' => $courseId,
            'total_amount' => $invoiceData['total_amount']
        ]);

        $invoice = Invoice::updateOrCreate(
            [
                'id' => $data['id'] ?? null,
                'school_id' => $data['school_id'],
            ],
            $invoiceData
        );

        Log::info('Invoice processed successfully', [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'student' => $invoice->student,
            'total_amount' => $invoice->total_amount
        ]);

        return ['success' => true, 'id' => $invoice->id, 'message' => 'Invoice processed'];

    } catch (\Exception $e) {
        Log::error('Error processing invoice', [
            'error' => $e->getMessage(),
            'data' => $data,
            'operation' => $operation,
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false, 
            'error' => $e->getMessage(),
            'debug_data' => $data
        ];
    }
}


// ALSO REPLACE your upsertPayment method to handle the same issue:

private function upsertPayment($data, $operation)
{
    if ($operation === 'delete') {
        Payment::where('id', $data['id'])->delete();
        return;
    }

    // ✅ CRITICAL: Check if invoice exists first (to avoid foreign key errors)
    if (isset($data['invoiceId'])) {
        $invoiceExists = Invoice::where('id', $data['invoiceId'])->exists();
        if (!$invoiceExists) {
            // Instead of throwing an error, log it and skip this payment
            Log::warning("Skipping payment {$data['id']} - Invoice {$data['invoiceId']} does not exist");
            return;
        }
    }

    // ✅ CRITICAL FIX: For partial updates, use direct update instead of updateOrCreate
    if ($operation === 'update') {
        $existingPayment = Payment::where('id', $data['id'])->first();
        
        if (!$existingPayment) {
            throw new \Exception("Cannot update payment {$data['id']} - payment does not exist");
        }
        
        $updateData = [];
        
        if (array_key_exists('receipt_path', $data)) {
            $updateData['receipt_path'] = $data['receipt_path'];
        }
        
        if (array_key_exists('receipt_generated', $data)) {
            $updateData['receipt_generated'] = $data['receipt_generated'];
        }
        
        if (array_key_exists('amount', $data)) {
            $updateData['amount'] = $data['amount'];
        }
        
        if (array_key_exists('notes', $data)) {
            $updateData['notes'] = $data['notes'];
        }
        
        if (array_key_exists('status', $data)) {
            $updateData['status'] = $data['status'];
        }
        
        // Only update if we have data to update
        if (!empty($updateData)) {
            $existingPayment->update($updateData);
        }
        
        return;
    }

    // For CREATE operations, ensure all required fields are present
    $updateFields = [];
    
    $updateFields['invoiceId'] = $data['invoiceId'] ?? null;  // Your table uses 'invoiceId'
    $updateFields['amount'] = $data['amount'] ?? 0;
    $updateFields['method'] = $data['method'] ?? 'cash';  // Your table uses 'method'
    $updateFields['paymentDate'] = $data['created_at'] ?? now();  // Your table uses 'paymentDate'
    $updateFields['status'] = $data['status'] ?? 'completed';
    $updateFields['notes'] = $data['notes'] ?? null;
    $updateFields['reference'] = $data['reference'] ?? null;
    $updateFields['receipt_path'] = $data['receipt_path'] ?? null;
    $updateFields['receipt_generated'] = $data['receipt_generated'] ?? false;
    $updateFields['userId'] = $data['userId'] ?? null;  // Your table uses 'userId'

    Payment::updateOrCreate(
        ['id' => $data['id']],
        $updateFields
    );
}

    public function status()
    {
        try {
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;

            if (!$schoolId) {
                return $this->sendError('User does not belong to any school.', [], 400);
            }

            $stats = [
                'users' => User::where('school_id', $schoolId)->count(),
                'courses' => Course::where('school_id', $schoolId)->count(),
                'fleet' => Fleet::where('school_id', $schoolId)->count(),
                'schedules' => Schedule::where('school_id', $schoolId)->count(),
                'invoices' => Invoice::where('school_id', $schoolId)->count(),
                'payments' => Payment::whereHas('user', function($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })->count(),
                'last_sync' => now()->toISOString(),
            ];

            return $this->sendResponse($stats, 'Sync status retrieved.');

        } catch (\Exception $e) {
            return $this->sendError('Failed to get sync status.', ['error' => $e->getMessage()], 500);
        }
    }
}
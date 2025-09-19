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


private function processIndividualItem($type, $data, $operation)
{
    try {
        switch ($type) {
            case 'users':
                return $this->upsertUser($data, $operation);
            case 'courses':
                return $this->upsertCourse($data, $operation);
            case 'fleet':
                return $this->upsertFleet($data, $operation);
            case 'schedules':
                return $this->upsertSchedule($data, $operation);
            case 'invoices':
                return $this->upsertInvoice($data, $operation);
            case 'payments':
                return $this->upsertPayment($data, $operation);
            default:
                throw new \Exception("Unknown data type: {$type}");
        }
    } catch (\Exception $e) {
        Log::error("Error processing {$type} item", [
            'error' => $e->getMessage(),
            'data' => $data,
            'operation' => $operation
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

        Log::info('Sync upload request', [
            'user_id' => $currentUser->id,
            'school_id' => $schoolId,
            'data_types' => array_keys($request->all())
        ]);

        if (!$schoolId) {
            return $this->sendError('User does not belong to any school.', [], 400);
        }

        $uploaded = 0;
        $errors = [];
        $successfulItems = []; // ✅ INITIALIZE THIS ARRAY

        // Process data types in dependency order
        $dataTypes = ['users', 'courses', 'fleet', 'invoices', 'schedules', 'payments'];
        
        foreach ($dataTypes as $type) {
            if ($request->has($type)) {
                $result = $this->processDataType($type, $request->$type, $schoolId);
                $uploaded += $result['uploaded'];
                
                // ✅ COLLECT SUCCESSFUL ITEMS
                if (isset($result['successful_items'])) {
                    $successfulItems = array_merge($successfulItems, $result['successful_items']);
                }
                
                if (!empty($result['errors'])) {
                    $errors[$type] = $result['errors'];
                }
            }
        }

        if (empty($errors)) {
            DB::commit();
            return $this->sendResponse([
                'uploaded' => $uploaded,
                'successful_items' => $successfulItems, // ✅ INCLUDE IN RESPONSE
                'timestamp' => now()->toISOString()
            ], 'Data uploaded successfully.');
        } else {
            DB::commit(); // Still commit partial successes
            return response()->json([
                'success' => false,
                'message' => 'Upload partially failed.',
                'data' => [
                    'uploaded' => $uploaded,
                    'errors' => $errors,
                    'successful_items' => $successfulItems, // ✅ INCLUDE EVEN ON PARTIAL FAILURE
                    'timestamp' => now()->toISOString()
                ]
            ], 422);
        }

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Sync upload failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return $this->sendError('Upload failed.', [
            'error' => $e->getMessage(),
            'successful_items' => [], // ✅ INCLUDE EVEN ON COMPLETE FAILURE
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



private function upsertUser($data, $operation)
{
    if ($operation === 'delete') {
        User::where('id', $data['id'])->where('school_id', $data['school_id'])->delete();
        return;
    }

    // ✅ FIXED: Check if email exists before accessing it
    if (!isset($data['email'])) {
        throw new \Exception('Email is required for user operations');
    }

    User::updateOrCreate(
        ['id' => $data['id']],
        [
            'fname' => $data['fname'] ?? '',
            'lname' => $data['lname'] ?? '',
            'email' => $data['email'],
            'role' => $data['role'] ?? 'student',
            'phone' => $data['phone'] ?? '',
            'password' => $data['password'],
            'status' => $data['status'] ?? 'active',
            'school_id' => $data['school_id'],
            'date_of_birth' => $data['date_of_birth'] ?? '2000-01-01',
            'gender' => $data['gender'] ?? 'other',
            'address' => $data['address'] ?? '',
            'idnumber' => $data['idnumber'] ?? null,
        ]
    );
}

private function upsertCourse($data, $operation)
{
    if ($operation === 'delete') {
        Course::where('id', $data['id'])->where('school_id', $data['school_id'])->delete();
        return;
    }

    // ✅ FIXED: Check if name exists and don't try to access email
    if (!isset($data['name'])) {
        throw new \Exception('Name is required for course operations');
    }

    Course::updateOrCreate(
        ['id' => $data['id']],
        [
            'name' => $data['name'],
            'price' => $data['price'] ?? 0,
            'status' => $data['status'] ?? 'active',
            'school_id' => $data['school_id'],
        ]
    );
}

private function upsertFleet($data, $operation)
{
    if ($operation === 'delete') {
        Fleet::where('id', $data['id'])->where('school_id', $data['school_id'])->delete();
        return;
    }

    // ✅ FIXED: Check required fields exist
    if (!isset($data['make']) || !isset($data['model'])) {
        throw new \Exception('Make and model are required for fleet operations');
    }

    Fleet::updateOrCreate(
        ['id' => $data['id']],
        [
            'make' => $data['make'],
            'model' => $data['model'],
            'modelyear' => $data['modelyear'] ?? $data['modelyear'] ?? date('Y'),
            'carplate' => $data['carplate'] ?? $data['carPlate'] ?? '',
            'status' => $data['status'] ?? 'available',
            'instructor' => $data['instructor'] ?? null, // ✅ FIXED: Handle null instructor
            'school_id' => $data['school_id'],
        ]
    );
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
            'vehicle' => $data['vehicle_id'] ?? $data['car'] ?? null,
            'start' => $data['start'],
            'end' => $data['end'],
            'status' => $data['status'] ?? 'scheduled',
            'class_type' => $data['class_type'] ?? 'Practical',
            'notes' => $data['notes'] ?? '',
            'school_id' => $data['school_id'],
        ]
    );
}

private function upsertInvoice($data, $operation)
{
    if ($operation === 'delete') {
        Invoice::where('id', $data['id'])->where('school_id', $data['school_id'])->delete();
        return;
    }

    Invoice::updateOrCreate(
        ['id' => $data['id']],
        [
            'student' => $data['student_id'] ?? $data['student'] ?? null,
            'course' => $data['course_id'] ?? $data['course'] ?? null,
            'lessons' => $data['lessons'] ?? $data['lessons'],
            'amountpaid' => $data['amountpaid'] ?? $data['amountpaid'],
            'invoice_number' => $data['invoice_number'] ?? $data['invoice_number'],
            'price_per_lesson' => $data['price_per_lesson'] ?? $data['price_per_lesson'],
            'total_amount' => $data['total_amount'] ?? $data['amount'] ?? 0,
            'status' => $data['status'] ?? 'pending',
            'due_date' => $data['due_date'] ?? null,
            'school_id' => $data['school_id'],
        ]
    );
}

private function upsertPayment($data, $operation)
{
    if ($operation === 'delete') {
        Payment::where('id', $data['id'])->delete();
        return;
    }

    Payment::updateOrCreate(
        ['id' => $data['id']],
        [
            'invoiceId' => $data['invoiceId'] ?? null,
            'student_id' => $data['student_id'] ?? null,
            'amount' => $data['amount'] ?? 0,
            'method' => $data['method'] ?? 'cash',
            'payment_date' => $data['payment_date'] ?? now(),
            'status' => $data['status'] ?? 'completed',
        ]
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
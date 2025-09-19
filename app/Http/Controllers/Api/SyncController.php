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

        if (!$schoolId) {
            return $this->sendError('User does not belong to any school.', [], 400);
        }

        $results = [];
        $allErrors = [];
        $totalUploaded = 0;

        // ✅ CRITICAL: Process in this exact order to avoid foreign key errors
        $processingOrder = [
            'users',      // First: Create users
            'courses',    // Second: Create courses  
            'fleet',      // Third: Create vehicles
            'invoices',   // Fourth: Create invoices (BEFORE payments)
            'payments',   // Fifth: Create payments (AFTER invoices)
            'schedules',  // Last: Create schedules (they reference everything)
        ];

        foreach ($processingOrder as $type) {
            if ($request->has($type)) {
                Log::info("Processing {$type} data");
                
                $result = $this->processDataType($type, $request->input($type), $schoolId);
                $results[$type] = $result;
                $totalUploaded += $result['uploaded'];
                
                if (!empty($result['errors'])) {
                    $allErrors[$type] = $result['errors'];
                }

                // Stop processing if critical errors in invoices (before payments)
                if ($type === 'invoices' && !empty($result['errors'])) {
                    Log::warning("Invoice errors detected, skipping payment processing to avoid foreign key errors");
                    break;
                }
            }
        }

        // Check if we have critical errors that should cause rollback
        $hasCriticalErrors = $this->checkForCriticalErrors($allErrors);
        
        if ($hasCriticalErrors && $totalUploaded === 0) {
            DB::rollback();
            return $this->sendError('Upload failed completely due to critical errors.', [
                'data' => [
                    'uploaded' => $totalUploaded,
                    'errors' => $allErrors,
                ]
            ], 422);
        }

        DB::commit();

        $responseMessage = empty($allErrors) ? 'Upload successful.' : 'Upload partially failed.';
        $responseCode = empty($allErrors) ? 200 : 422;

        return response()->json([
            'success' => $totalUploaded > 0,
            'message' => $responseMessage,
            'data' => [
                'uploaded' => $totalUploaded,
                'errors' => $allErrors,
                'successful_items' => [], // You can populate this if needed
                'timestamp' => now()->toISOString(),
            ]
        ], $responseCode);

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Sync upload failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return $this->sendError('Upload failed.', ['error' => $e->getMessage()], 500);
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
            'car' => $data['vehicle_id'] ?? $data['car'] ?? null,
            'is_recurring' => $data['is_recurring'] ?? 0,
            'recurring_pattern' => $data['recurring_pattern'] ?? null,
            'recurring_end_date ' => $data['recurring_end_date'] ?? null,
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



private function upsertInvoice($data, $operation)
{
    if ($operation === 'delete') {
        Invoice::where('id', $data['id'])->where('school_id', $data['school_id'])->delete();
        return;
    }

    // ✅ CRITICAL FIX: For partial updates, use direct update instead of updateOrCreate
    if ($operation === 'update') {
        // Check if record exists first
        $existingInvoice = Invoice::where('id', $data['id'])->first();
        
        if (!$existingInvoice) {
            throw new \Exception("Cannot update invoice {$data['id']} - invoice does not exist");
        }
        
        // Build update array with only the fields that are present
        $updateData = [];
        
        if (array_key_exists('amountpaid', $data)) {
            $updateData['amountpaid'] = $data['amountpaid'];
        }
        
        if (array_key_exists('status', $data)) {
            $updateData['status'] = $data['status'];
        }
        
        if (array_key_exists('total_amount', $data)) {
            $updateData['total_amount'] = $data['total_amount'];
        }
        
        if (array_key_exists('lessons', $data)) {
            $updateData['lessons'] = $data['lessons'];
        }
        
        if (array_key_exists('price_per_lesson', $data)) {
            $updateData['price_per_lesson'] = $data['price_per_lesson'];
        }
        
        if (array_key_exists('due_date', $data)) {
            $updateData['due_date'] = $data['due_date'];
        }
        
        // Only update if we have data to update
        if (!empty($updateData)) {
            $existingInvoice->update($updateData);
        }
        
        return;
    }
    
    // For CREATE operations, use updateOrCreate with all required fields
    $updateFields = [
        'school_id' => $data['school_id'],
    ];
    
    // Set required fields with defaults for create operations
    $updateFields['invoice_number'] = $data['invoice_number'] ?? 'AUTO-' . time();
    $updateFields['student'] = $data['student'] ?? $data['student_id'] ?? null;
    $updateFields['course'] = $data['course'] ?? $data['course_id'] ?? null;
    $updateFields['lessons'] = $data['lessons'] ?? 1;
    $updateFields['price_per_lesson'] = $data['price_per_lesson'] ?? 0;
    $updateFields['total_amount'] = $data['total_amount'] ?? $data['amount'] ?? 0;
    $updateFields['amountpaid'] = $data['amountpaid'] ?? 0;
    $updateFields['status'] = $data['status'] ?? 'unpaid';
    $updateFields['due_date'] = $data['due_date'] ?? now()->addDays(30);
    
    Invoice::updateOrCreate(
        ['id' => $data['id']],
        $updateFields
    );
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
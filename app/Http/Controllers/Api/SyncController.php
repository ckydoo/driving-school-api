<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Fleet;
use App\Models\Schedule;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SyncController extends Controller
{
    /**
     * Download data from server based on last sync timestamp
     */
    public function download(Request $request)
    {
        try {
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;

            if (!$schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not belong to any school.',
                ], 400);
            }

            $lastSync = $request->header('Last-Sync');
            $syncTimestamp = now()->toISOString();

            Log::info('Sync download started', [
                'school_id' => $schoolId,
                'last_sync' => $lastSync,
                'sync_timestamp' => $syncTimestamp
            ]);

            // Build query constraint for incremental sync
            $whereClause = function ($query) use ($schoolId, $lastSync) {
                $query->where('school_id', $schoolId);
                if ($lastSync && $lastSync !== 'Never') {
                    $query->where('updated_at', '>', $lastSync);
                }
            };

            $data = [
                'users' => User::where($whereClause)->get()->toArray(),
                'courses' => Course::where($whereClause)->get()->toArray(),
                'fleet' => Fleet::where($whereClause)->get()->toArray(),
                'schedules' => Schedule::where($whereClause)->get()->toArray(),
                'invoices' => Invoice::where($whereClause)->get()->toArray(),
                'payments' => Payment::whereHas('invoice', function($q) use ($schoolId) {
                    $q->whereHas('student', function($sq) use ($schoolId) {
                        $sq->where('school_id', $schoolId);
                    });
                })->when($lastSync && $lastSync !== 'Never', function($q) use ($lastSync) {
                    $q->where('updated_at', '>', $lastSync);
                })->get()->toArray(),
                'sync_timestamp' => $syncTimestamp,
            ];

            $totalRecords = array_sum(array_map('count', array_filter($data, 'is_array')));

            Log::info('Sync download completed', [
                'school_id' => $schoolId,
                'total_records' => $totalRecords,
                'last_sync' => $lastSync
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Download completed successfully.',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Sync download failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Download failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload changes to server with proper ID mapping and dependency handling
     */
    public function upload(Request $request)
    {
        DB::beginTransaction();

        try {
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;
            $changes = $request->get('changes', []);

            if (!$schoolId) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'User does not belong to any school.',
                ], 400);
            }

            if (empty($changes)) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'No changes provided for upload.',
                ], 400);
            }

            Log::info('Sync upload started', [
                'school_id' => $schoolId,
                'changes_count' => count($changes)
            ]);

            $results = [];
            $allErrors = [];
            $totalUploaded = 0;
            $totalProcessed = 0;
            
            // Track ID mappings for maintaining relationships
            $idMappings = [
                'users' => [],
                'courses' => [],
                'fleet' => [],
                'invoices' => [],
                'payments' => [],
                'schedules' => []
            ];

            // Process in dependency order to maintain referential integrity
            $processingOrder = ['users', 'courses', 'fleet', 'invoices', 'payments', 'schedules'];
            
            // Group changes by type
            $changesByType = [];
            foreach ($changes as $change) {
                $type = $change['table'] ?? $change['type'] ?? 'unknown';
                if (!isset($changesByType[$type])) {
                    $changesByType[$type] = [];
                }
                $changesByType[$type][] = $change;
            }

            // Process each type in dependency order
            foreach ($processingOrder as $type) {
                if (!isset($changesByType[$type])) continue;
                
                Log::info("Processing {$type}", ['count' => count($changesByType[$type])]);
                
                foreach ($changesByType[$type] as $change) {
                    try {
                        $totalProcessed++;
                        $data = $change['data'] ?? $change;
                        $operation = $change['operation'] ?? 'upsert';
                        $localId = $data['id'] ?? null;
                        
                        // Apply ID mappings for foreign keys
                        $data = $this->applyIdMappings($data, $type, $idMappings);
                        
                        // Ensure school_id is set
                        $data['school_id'] = $schoolId;
                        
                        // Process the item
                        $result = $this->processIndividualItem($type, $data, $operation);
                        
                        if ($result['success']) {
                            $totalUploaded++;
                            $serverId = $result['id'] ?? null;
                            
                            // Store ID mapping if IDs differ
                            if ($localId && $serverId && $localId != $serverId) {
                                $idMappings[$type][$localId] = $serverId;
                                Log::info("ID mapping created", [
                                    'type' => $type,
                                    'local_id' => $localId,
                                    'server_id' => $serverId
                                ]);
                            }
                            
                            $results[] = [
                                'type' => $type,
                                'local_id' => $localId,
                                'server_id' => $serverId,
                                'status' => 'success',
                                'operation' => $operation
                            ];
                        } else {
                            $allErrors[] = [
                                'type' => $type,
                                'local_id' => $localId,
                                'error' => $result['error'] ?? 'Processing failed',
                                'operation' => $operation,
                                'item' => ['data' => $data]
                            ];
                            
                            Log::warning("Failed to process {$type}", [
                                'local_id' => $localId,
                                'error' => $result['error'] ?? 'Unknown error'
                            ]);
                        }
                        
                    } catch (\Exception $e) {
                        Log::error("Exception processing {$type}", [
                            'error' => $e->getMessage(),
                            'change' => $change,
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        $allErrors[] = [
                            'type' => $type,
                            'error' => $e->getMessage(),
                            'operation' => $operation ?? 'unknown',
                            'item' => ['data' => $change['data'] ?? $change]
                        ];
                    }
                }
            }

            // Determine final result
            $hasErrors = !empty($allErrors);
            $hasSuccesses = $totalUploaded > 0;
            
            if ($hasSuccesses && !$hasErrors) {
                // Complete success
                DB::commit();
                
                Log::info('Sync upload completed successfully', [
                    'school_id' => $schoolId,
                    'uploaded' => $totalUploaded,
                    'processed' => $totalProcessed
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Upload completed successfully.',
                    'data' => [
                        'uploaded' => $totalUploaded,
                        'processed' => $totalProcessed,
                        'errors' => [],
                        'successful_items' => $results,
                        'id_mappings' => $idMappings,
                        'timestamp' => now()->toISOString(),
                    ]
                ], 200);
                
            } elseif ($hasSuccesses && $hasErrors) {
                // Partial success
                DB::commit();
                
                Log::warning('Sync upload partially completed', [
                    'school_id' => $schoolId,
                    'uploaded' => $totalUploaded,
                    'errors' => count($allErrors)
                ]);
                
                return response()->json([
                    'success' => true,
                    'partial' => true,
                    'message' => 'Upload partially completed.',
                    'data' => [
                        'uploaded' => $totalUploaded,
                        'processed' => $totalProcessed,
                        'errors' => $allErrors,
                        'successful_items' => $results,
                        'id_mappings' => $idMappings,
                        'timestamp' => now()->toISOString(),
                    ]
                ], 200);
                
            } else {
                // Complete failure
                DB::rollback();
                
                Log::error('Sync upload failed completely', [
                    'school_id' => $schoolId,
                    'errors' => count($allErrors)
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed completely.',
                    'data' => [
                        'uploaded' => 0,
                        'processed' => $totalProcessed,
                        'errors' => $allErrors,
                        'timestamp' => now()->toISOString(),
                    ]
                ], 400);
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Sync upload exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply ID mappings to foreign keys in data
     */
    private function applyIdMappings($data, $type, $idMappings)
    {
        switch ($type) {
            case 'invoices':
                // Map student ID
                if (isset($data['student']) && isset($idMappings['users'][$data['student']])) {
                    $oldId = $data['student'];
                    $data['student'] = $idMappings['users'][$data['student']];
                    Log::info("Mapped invoice student ID: {$oldId} -> {$data['student']}");
                }
                
                // Map course ID
                if (isset($data['course']) && isset($idMappings['courses'][$data['course']])) {
                    $oldId = $data['course'];
                    $data['course'] = $idMappings['courses'][$data['course']];
                    Log::info("Mapped invoice course ID: {$oldId} -> {$data['course']}");
                }
                break;
                
            case 'payments':
                // Map invoice ID
                if (isset($data['invoiceId']) && isset($idMappings['invoices'][$data['invoiceId']])) {
                    $oldId = $data['invoiceId'];
                    $data['invoiceId'] = $idMappings['invoices'][$data['invoiceId']];
                    Log::info("Mapped payment invoice ID: {$oldId} -> {$data['invoiceId']}");
                }
                
                // Map user ID
                if (isset($data['userId']) && isset($idMappings['users'][$data['userId']])) {
                    $oldId = $data['userId'];
                    $data['userId'] = $idMappings['users'][$data['userId']];
                    Log::info("Mapped payment user ID: {$oldId} -> {$data['userId']}");
                }
                break;
                
            case 'schedules':
                // Map student ID
                if (isset($data['student']) && isset($idMappings['users'][$data['student']])) {
                    $oldId = $data['student'];
                    $data['student'] = $idMappings['users'][$data['student']];
                    Log::info("Mapped schedule student ID: {$oldId} -> {$data['student']}");
                }
                
                // Map instructor ID
                if (isset($data['instructor']) && isset($idMappings['users'][$data['instructor']])) {
                    $oldId = $data['instructor'];
                    $data['instructor'] = $idMappings['users'][$data['instructor']];
                    Log::info("Mapped schedule instructor ID: {$oldId} -> {$data['instructor']}");
                }
                
                // Map course ID
                if (isset($data['course']) && isset($idMappings['courses'][$data['course']])) {
                    $oldId = $data['course'];
                    $data['course'] = $idMappings['courses'][$data['course']];
                    Log::info("Mapped schedule course ID: {$oldId} -> {$data['course']}");
                }
                
                // Map vehicle ID
                if (isset($data['car']) && isset($idMappings['fleet'][$data['car']])) {
                    $oldId = $data['car'];
                    $data['car'] = $idMappings['fleet'][$data['car']];
                    Log::info("Mapped schedule vehicle ID: {$oldId} -> {$data['car']}");
                }
                break;
        }
        
        return $data;
    }

    /**
     * Process individual item based on type
     */
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
                    return [
                        'success' => false,
                        'error' => "Unknown item type: {$type}"
                    ];
            }
        } catch (\Exception $e) {
            Log::error("Error processing {$type}", [
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

    /**
     * Upsert user record
     */
    private function upsertUser($data, $operation)
    {
        if ($operation === 'delete') {
            User::where('id', $data['id'])->where('school_id', $data['school_id'])->delete();
            return ['success' => true, 'id' => $data['id'], 'message' => 'User deleted'];
        }

        // Validate required fields
        if (empty($data['email']) || empty($data['fname'])) {
            return ['success' => false, 'error' => 'Email and first name are required'];
        }

        // Check for duplicate email within school
        $existingUser = User::where('email', $data['email'])
                           ->where('school_id', $data['school_id'])
                           ->where('id', '!=', $data['id'] ?? 0)
                           ->first();
                           
        if ($existingUser) {
            return ['success' => false, 'error' => 'Email already exists in this school'];
        }

        $user = User::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'school_id' => $data['school_id'],
                'fname' => $data['fname'],
                'lname' => $data['lname'] ?? '',
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'] ?? 'student',
                'status' => $data['status'] ?? 'active',
                'idnumber' => $data['idnumber'] ?? null,
                'address' => $data['address'] ?? null,
                'password'=> $data['password'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,

            ]
        );

        return ['success' => true, 'id' => $user->id, 'message' => 'User processed'];
    }

    /**
     * Upsert course record
     */
    private function upsertCourse($data, $operation)
    {
        if ($operation === 'delete') {
            Course::where('id', $data['id'])->where('school_id', $data['school_id'])->delete();
            return ['success' => true, 'id' => $data['id'], 'message' => 'Course deleted'];
        }

        if (empty($data['name'])) {
            return ['success' => false, 'error' => 'Course name is required'];
        }

        $course = Course::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'school_id' => $data['school_id'],
                'name' => $data['name'],
                'price' => $data['price'] ?? 0,
                'status' => $data['status'] ?? 'active',
            ]
        );

        return ['success' => true, 'id' => $course->id, 'message' => 'Course processed'];
    }

    /**
     * Upsert fleet record
     */
    private function upsertFleet($data, $operation)
    {
        if ($operation === 'delete') {
            Fleet::where('id', $data['id'])->where('school_id', $data['school_id'])->delete();
            return ['success' => true, 'id' => $data['id'], 'message' => 'Vehicle deleted'];
        }

        if (empty($data['carplate']) || empty($data['make'])) {
            return ['success' => false, 'error' => 'Car plate and make are required'];
        }

        // Check for duplicate carplate within school
        $existingFleet = Fleet::where('carplate', $data['carplate'])
                             ->where('school_id', $data['school_id'])
                             ->where('id', '!=', $data['id'] ?? 0)
                             ->first();
                             
        if ($existingFleet) {
            return ['success' => false, 'error' => 'Car plate already exists in this school'];
        }

        $fleet = Fleet::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'school_id' => $data['school_id'],
                'carplate' => $data['carplate'],
                'make' => $data['make'],
                'model' => $data['model'] ?? '',
                'modelyear' => $data['modelyear'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]
        );

        return ['success' => true, 'id' => $fleet->id, 'message' => 'Vehicle processed'];
    }

    /**
     * Upsert schedule record
     */
    private function upsertSchedule($data, $operation)
    {
        if ($operation === 'delete') {
            Schedule::where('id', $data['id'])->delete();
            return ['success' => true, 'id' => $data['id'], 'message' => 'Schedule deleted'];
        }

        // Validate foreign key references
        if (isset($data['student']) && !User::where('id', $data['student'])->exists()) {
            return ['success' => false, 'error' => 'Referenced student does not exist'];
        }
        
        if (isset($data['instructor']) && !User::where('id', $data['instructor'])->exists()) {
            return ['success' => false, 'error' => 'Referenced instructor does not exist'];
        }
        
        if (isset($data['course']) && !Course::where('id', $data['course'])->exists()) {
            return ['success' => false, 'error' => 'Referenced course does not exist'];
        }
        
        if (isset($data['car']) && !Fleet::where('id', $data['car'])->exists()) {
            return ['success' => false, 'error' => 'Referenced vehicle does not exist'];
        }

        $schedule = Schedule::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'school_id' =>$data['school_id'],
                'student' => $data['student'] ?? null,
                'instructor' => $data['instructor'] ?? null,
                'course' => $data['course'] ?? null,
                'car' => $data['car'] ?? null,
                'start' => $data['start'],
                'end' => $data['end'],
                'status' => $data['status'] ?? 'scheduled',
                'class_type' => $data['class_type'] ?? 'practical',
                'attended' => $data['attended'] ?? 0,
                'notes' => $data['notes'] ?? '',
                'is_recurring' => $data['is_recurring'] ?? 0,
                'recurrence_pattern' => $data['recurrence_pattern'] ?? null,
                'recurrence_end_date' => $data['recurrence_end_date'] ?? null,
            ]
        );

        return ['success' => true, 'id' => $schedule->id, 'message' => 'Schedule processed'];
    }

    /**
 * Fixed: Upsert invoice record with proper school_id handling
 */
private function upsertInvoice($data, $operation)
{
    Log::info('Processing invoice upsert', [
        'operation' => $operation,
        'school_id' => $data['school_id'] ?? 'missing',
        'student_id' => $data['student'] ?? 'missing',
        'course_id' => $data['course'] ?? 'missing'
    ]);

    if ($operation === 'delete') {
        Invoice::where('id', $data['id'])->delete();
        return ['success' => true, 'id' => $data['id'], 'message' => 'Invoice deleted'];
    }

    // Validate required fields
    if (empty($data['student']) || empty($data['total_amount'])) {
        return ['success' => false, 'error' => 'Student and total amount are required'];
    }

    // ✅ CRITICAL: Ensure school_id is set
    if (empty($data['school_id'])) {
        return ['success' => false, 'error' => 'School ID is required'];
    }

    try {
        // Validate student exists and belongs to the correct school
        $student = User::where('id', $data['student'])
                       ->where('school_id', $data['school_id'])
                       ->first();
        
        if (!$student) {
            return ['success' => false, 'error' => 'Referenced student does not exist or belongs to different school'];
        }
        
        // Validate course if provided
        $course = null;
        if (isset($data['course']) && $data['course']) {
            $course = Course::where('id', $data['course'])
                           ->where('school_id', $data['school_id'])
                           ->first();
            
            if (!$course) {
                return ['success' => false, 'error' => 'Referenced course does not exist or belongs to different school'];
            }
        }

        // Create or update the invoice
        $invoiceData = [
            'school_id' => $data['school_id'], // ✅ CRITICAL: Include school_id
            'student' => $data['student'],
            'course' => $data['course'] ?? null,
            'invoice_number' => $data['invoice_number'] ?? 'INV-' . time(),
            'lessons' => $data['lessons'] ?? 0,
            'price_per_lesson' => $data['price_per_lesson'] ?? 0,
            'total_amount' => $data['total_amount'],
            'amountpaid' => $data['amountpaid'] ?? 0,
            'status' => $data['status'] ?? 'pending',
            'due_date' => $data['due_date'] ?? now()->addDays(30),
            'used_lessons' => $data['used_lessons'] ?? 0,
            'courseName' => $course ? $course->name : null,
        ];

        $invoice = Invoice::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $invoiceData
        );

        Log::info('Invoice processed successfully', [
            'id' => $invoice->id,
            'school_id' => $invoice->school_id,
            'student_id' => $invoice->student
        ]);

        return ['success' => true, 'id' => $invoice->id, 'message' => 'Invoice processed'];
        
    } catch (\Exception $e) {
        Log::error('Invoice upsert failed', [
            'error' => $e->getMessage(),
            'data' => $data,
            'trace' => $e->getTraceAsString()
        ]);
        
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

    /**
     * Upsert payment record
     */
    private function upsertPayment($data, $operation)
    {
        if ($operation === 'delete') {
            Payment::where('id', $data['id'])->delete();
            return ['success' => true, 'id' => $data['id'], 'message' => 'Payment deleted'];
        }

        // Validate required fields
        if (empty($data['amount'])) {
            return ['success' => false, 'error' => 'Amount is required'];
        }

        // Validate foreign key references
        if (isset($data['invoiceId'])) {
            $invoice = Invoice::find($data['invoiceId']);
            if (!$invoice) {
                return ['success' => false, 'error' => 'Referenced invoice does not exist'];
            }
            
            // Verify invoice belongs to same school
            $student = User::find($invoice->student);
            if (!$student || $student->school_id !== $data['school_id']) {
                return ['success' => false, 'error' => 'Referenced invoice belongs to different school'];
            }
        }
        
        if (isset($data['userId']) && !User::where('id', $data['userId'])->where('school_id', $data['school_id'])->exists()) {
            return ['success' => false, 'error' => 'Referenced user does not exist or belongs to different school'];
        }

        // For updates, handle partial data
        if ($operation === 'update' && isset($data['id'])) {
            $existingPayment = Payment::find($data['id']);
            if (!$existingPayment) {
                return ['success' => false, 'error' => 'Payment to update does not exist'];
            }
            
            $updateData = [];
            $allowedFields = ['amount', 'method', 'paymentDate', 'status', 'notes', 'reference', 'receipt_path', 'receipt_generated'];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            if (!empty($updateData)) {
                $existingPayment->update($updateData);
            }
            
            return ['success' => true, 'id' => $existingPayment->id, 'message' => 'Payment updated'];
        }

        // For create operations
        $payment = Payment::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'invoiceId' => $data['invoiceId'] ?? null,
                'amount' => $data['amount'],
                'method' => $data['method'] ?? 'cash',
                'paymentDate' => $data['paymentDate'] ?? now(),
                'status' => $data['status'] ?? 'completed',
                'notes' => $data['notes'] ?? '',
                'reference' => $data['reference'] ?? null,
                'receipt_path' => $data['receipt_path'] ?? null,
                'receipt_generated' => $data['receipt_generated'] ?? false,
                'userId' => $data['userId'] ?? null,
            ]
        );

        return ['success' => true, 'id' => $payment->id, 'message' => 'Payment processed'];
    }

    /**
     * Get sync status/statistics
     */
    public function status()
    {
        try {
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;

            if (!$schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not belong to any school.',
                ], 400);
            }

            $stats = [
                'users' => User::where('school_id', $schoolId)->count(),
                'courses' => Course::where('school_id', $schoolId)->count(),
                'fleet' => Fleet::where('school_id', $schoolId)->count(),
                'schedules' => Schedule::whereHas('studentUser', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->count(),
                'invoices' => Invoice::whereHas('student', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->count(),
                'payments' => Payment::whereHas('invoice.student', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->count(),
                'last_sync' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Sync status retrieved.',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get sync status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
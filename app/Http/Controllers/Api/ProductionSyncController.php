<?php
// app/Http/Controllers/Api/ProductionSyncController.php

namespace App\Http\Controllers\Api;

use App\Models\{User, Course, Fleet, Schedule, Invoice, Payment, DeviceRegistration};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log, Cache};
use Carbon\Carbon;

class ProductionSyncController extends BaseController
{
    /**
     * Get comprehensive sync state for a school and device
     */
    public function getSchoolSyncState(Request $request)
    {
        try {
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;
            $deviceId = $request->get('device_id');

            if (!$schoolId) {
                return $this->sendError('User does not belong to any school.', [], 400);
            }

            Log::info('Getting sync state', [
                'school_id' => $schoolId,
                'device_id' => $deviceId,
                'user_id' => $currentUser->id
            ]);

            // Get table versions (content hashes)
            $tableVersions = $this->generateTableVersions($schoolId);
            
            // Get device sync info
            $deviceInfo = $this->getOrCreateDeviceInfo($schoolId, $deviceId);
            
            // Check if device needs full sync
            $requiresFullSync = $this->checkIfDeviceRequiresFullSync($deviceInfo);

            $syncState = [
                'school_id' => $schoolId,
                'device_id' => $deviceId,
                'sync_version' => 1, // Increment this when you make breaking changes
                'table_versions' => $tableVersions,
                'requires_full_sync' => $requiresFullSync,
                'server_time' => now()->toISOString(),
                'data_counts' => $this->getDataCounts($schoolId),
            ];

            return $this->sendResponse($syncState, 'Sync state retrieved successfully.');

        } catch (\Exception $e) {
            Log::error('Error getting sync state', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('Failed to get sync state.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Register a new device for sync
     */
    public function registerDevice(Request $request)
{
    try {
        $currentUser = auth()->user();
        $schoolId = $currentUser->school_id;
        $deviceId = $request->get('device_id');
        $platform = $request->get('platform', 'unknown');
        $appVersion = $request->get('app_version', '1.0.0');

        // Enhanced debugging
        Log::info('=== DEVICE REGISTRATION REQUEST ===', [
            'user_id' => $currentUser->id,
            'user_email' => $currentUser->email,
            'school_id' => $schoolId,
            'device_id' => $deviceId,
            'platform' => $platform,
            'app_version' => $appVersion,
            'request_body' => $request->all(),
            'timestamp' => now()->toISOString(),
        ]);

        if (!$schoolId || !$deviceId) {
            Log::error('Device registration failed - missing required fields', [
                'school_id' => $schoolId,
                'device_id' => $deviceId,
            ]);
            return $this->sendError('School ID and Device ID are required.', [], 400);
        }

        // Create or update device registration
        $device = DeviceRegistration::updateOrCreate(
            [
                'school_id' => $schoolId,
                'device_id' => $deviceId,
            ],
            [
                'user_id' => $currentUser->id,
                'platform' => $platform,
                'app_version' => $appVersion,
                'last_seen' => now(),
                'status' => 'active',
            ]
        );

        // Verify the device was actually saved
        $savedDevice = DeviceRegistration::where('school_id', $schoolId)
            ->where('device_id', $deviceId)
            ->first();

        if (!$savedDevice) {
            Log::error('Device registration failed - not found in database after save', [
                'school_id' => $schoolId,
                'device_id' => $deviceId,
            ]);
            return $this->sendError('Failed to save device registration.', [], 500);
        }

        Log::info('=== DEVICE REGISTRATION SUCCESS ===', [
            'id' => $savedDevice->id,
            'school_id' => $savedDevice->school_id,
            'device_id' => $savedDevice->device_id,
            'platform' => $savedDevice->platform,
            'created_at' => $savedDevice->created_at,
            'updated_at' => $savedDevice->updated_at,
        ]);

        return $this->sendResponse([
            'device_id' => $device->device_id,
            'registered_at' => $device->created_at->toISOString(),
            'status' => 'active',
        ], 'Device registered successfully.');

    } catch (\Exception $e) {
        Log::error('=== DEVICE REGISTRATION ERROR ===', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return $this->sendError('Failed to register device.', ['error' => $e->getMessage()], 500);
    }
}

    /**
     * Download all school data (for first-time setup)
     */
    public function downloadAllSchoolData(Request $request)
    {
        try {
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;

            if (!$schoolId) {
                return $this->sendError('User does not belong to any school.', [], 400);
            }

            Log::info('Downloading all school data', [
                'school_id' => $schoolId,
                'user_id' => $currentUser->id
            ]);

            // Get all data for the school
            $data = [
                'users' => User::where('school_id', $schoolId)
                    ->select('id', 'school_id', 'fname', 'lname', 'email', 'role', 'phone', 'status', 'date_of_birth', 'gender', 'address', 'idnumber', 'created_at', 'updated_at')
                    ->get()
                    ->toArray(),
                    
                'courses' => Course::where('school_id', $schoolId)
                    ->get()
                    ->toArray(),
                    
                'fleet' => Fleet::where('school_id', $schoolId)
                    ->get()
                    ->toArray(),
                    
                'schedules' => Schedule::with(['student:id,fname,lname', 'instructor:id,fname,lname', 'course:id,name', 'car:id,make,model'])
                    ->where('school_id', $schoolId)
                    ->get()
                    ->toArray(),
                    
                'invoices' => Invoice::with(['student:id,fname,lname', 'course:id,name', 'payments'])
                    ->where('school_id', $schoolId)
                    ->get()
                    ->toArray(),
                    
                'payments' => Payment::with(['invoice', 'user:id,fname,lname'])
                    ->whereHas('user', function($query) use ($schoolId) {
                        $query->where('school_id', $schoolId);
                    })
                    ->get()
                    ->toArray(),
                    
                'sync_timestamp' => now()->toISOString(),
                'table_versions' => $this->generateTableVersions($schoolId),
            ];

            // Log data counts
            $counts = [];
            foreach (['users', 'courses', 'fleet', 'schedules', 'invoices', 'payments'] as $table) {
                $counts[$table] = count($data[$table]);
            }

            Log::info('All school data downloaded', [
                'school_id' => $schoolId,
                'counts' => $counts,
                'total_records' => array_sum($counts)
            ]);

            return $this->sendResponse($data, 'All school data downloaded successfully.');

        } catch (\Exception $e) {
            Log::error('Error downloading all school data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Failed to download school data.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download incremental changes since last sync
     */
    public function downloadIncrementalChanges(Request $request)
    {
        try {
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;
            $since = $request->get('since');

            if (!$schoolId) {
                return $this->sendError('User does not belong to any school.', [], 400);
            }

            // Parse since timestamp
            $sinceDate = $since 
                ? Carbon::parse($since)
                : Carbon::now()->subYears(10); // Get all data if no since date

            Log::info('Downloading incremental changes', [
                'school_id' => $schoolId,
                'since' => $since,
                'parsed_since' => $sinceDate->toISOString()
            ]);

            // Get changed data since the timestamp
            $data = [
                'users' => User::where('school_id', $schoolId)
                    ->where('updated_at', '>', $sinceDate)
                    ->select('id', 'school_id', 'fname', 'lname', 'email', 'role', 'phone', 'status', 'date_of_birth', 'gender', 'address', 'idnumber', 'created_at', 'updated_at')
                    ->get()
                    ->toArray(),
                    
                'courses' => Course::where('school_id', $schoolId)
                    ->where('updated_at', '>', $sinceDate)
                    ->get()
                    ->toArray(),
                    
                'fleet' => Fleet::where('school_id', $schoolId)
                    ->where('updated_at', '>', $sinceDate)
                    ->get()
                    ->toArray(),
                    
                'schedules' => Schedule::with(['student:id,fname,lname', 'instructor:id,fname,lname', 'course:id,name', 'car:id,make,model'])
                    ->where('school_id', $schoolId)
                    ->where('updated_at', '>', $sinceDate)
                    ->get()
                    ->toArray(),
                    
                'invoices' => Invoice::with(['student:id,fname,lname', 'course:id,name', 'payments'])
                    ->where('school_id', $schoolId)
                    ->where('updated_at', '>', $sinceDate)
                    ->get()
                    ->toArray(),
                    
                'payments' => Payment::with(['invoice', 'user:id,fname,lname'])
                    ->whereHas('user', function($query) use ($schoolId) {
                        $query->where('school_id', $schoolId);
                    })
                    ->where('updated_at', '>', $sinceDate)
                    ->get()
                    ->toArray(),
                    
                'sync_timestamp' => now()->toISOString(),
                'table_versions' => $this->generateTableVersions($schoolId),
            ];

            // Log change counts
            $counts = [];
            foreach (['users', 'courses', 'fleet', 'schedules', 'invoices', 'payments'] as $table) {
                $counts[$table] = count($data[$table]);
            }

            Log::info('Incremental changes downloaded', [
                'school_id' => $schoolId,
                'since' => $since,
                'counts' => $counts,
                'total_changes' => array_sum($counts)
            ]);

            return $this->sendResponse($data, 'Incremental changes downloaded successfully.');

        } catch (\Exception $e) {
            Log::error('Error downloading incremental changes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Failed to download changes.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download specific table data
     */
    public function downloadTableData(Request $request)
    {
        try {
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;
            $table = $request->get('table');

            if (!$schoolId || !$table) {
                return $this->sendError('School ID and table name are required.', [], 400);
            }

            $allowedTables = ['users', 'courses', 'fleet', 'schedules', 'invoices', 'payments'];
            if (!in_array($table, $allowedTables)) {
                return $this->sendError('Invalid table name.', [], 400);
            }

            Log::info('Downloading table data', [
                'school_id' => $schoolId,
                'table' => $table
            ]);

            $data = [];

            switch ($table) {
                case 'users':
                    $data = User::where('school_id', $schoolId)
                        ->select('id', 'school_id', 'fname', 'lname', 'email', 'role', 'phone', 'status', 'date_of_birth', 'gender', 'address', 'idnumber', 'created_at', 'updated_at')
                        ->get()
                        ->toArray();
                    break;

                case 'courses':
                    $data = Course::where('school_id', $schoolId)->get()->toArray();
                    break;

                case 'fleet':
                    $data = Fleet::where('school_id', $schoolId)->get()->toArray();
                    break;

                case 'schedules':
                    $data = Schedule::with(['student:id,fname,lname', 'instructor:id,fname,lname', 'course:id,name', 'car:id,make,model'])
                        ->where('school_id', $schoolId)
                        ->get()
                        ->toArray();
                    break;

                case 'invoices':
                    $data = Invoice::with(['student:id,fname,lname', 'course:id,name', 'payments'])
                        ->where('school_id', $schoolId)
                        ->get()
                        ->toArray();
                    break;

                case 'payments':
                    $data = Payment::with(['invoice', 'user:id,fname,lname'])
                        ->whereHas('user', function($query) use ($schoolId) {
                            $query->where('school_id', $schoolId);
                        })
                        ->get()
                        ->toArray();
                    break;
            }

            Log::info('Table data downloaded', [
                'school_id' => $schoolId,
                'table' => $table,
                'count' => count($data)
            ]);

            return $this->sendResponse($data, "Table $table data downloaded successfully.");

        } catch (\Exception $e) {
            Log::error('Error downloading table data', [
                'table' => $request->get('table'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Failed to download table data.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload changes from client
     */
    public function uploadChanges(Request $request)
    {
        DB::beginTransaction();

        try {
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;
            $changes = $request->get('changes', []);

            if (!$schoolId) {
                return $this->sendError('User does not belong to any school.', [], 400);
            }

            Log::info('Uploading changes', [
                'school_id' => $schoolId,
                'user_id' => $currentUser->id,
                'change_groups' => count($changes)
            ]);

            $results = [
                'uploaded' => 0,
                'errors' => [],
                'processed_items' => 0,
            ];

            // Process each change group
            foreach ($changes as $changeGroup) {
                if (!isset($changeGroup['type']) || !isset($changeGroup['items'])) {
                    continue;
                }

                $type = $changeGroup['type'];
                $items = $changeGroup['items'];

                foreach ($items as $item) {
                    try {
                        $results['processed_items']++;
                        
                        // Ensure the item belongs to the correct school
                        $item['data']['school_id'] = $schoolId;
                        
                        $success = $this->processUploadItem($type, $item);
                        
                        if ($success) {
                            $results['uploaded']++;
                        } else {
                            $results['errors'][] = [
                                'type' => $type,
                                'item_id' => $item['id'] ?? 'unknown',
                                'error' => 'Processing failed'
                            ];
                        }
                    } catch (\Exception $e) {
                        $results['errors'][] = [
                            'type' => $type,
                            'item_id' => $item['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ];
                    }
                }
            }

            DB::commit();

            Log::info('Changes upload completed', [
                'school_id' => $schoolId,
                'uploaded' => $results['uploaded'],
                'errors' => count($results['errors']),
                'processed' => $results['processed_items']
            ]);

            return $this->sendResponse($results, 'Changes uploaded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error uploading changes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Failed to upload changes.', ['error' => $e->getMessage()], 500);
        }
    }

    // ===================================================================
    // HELPER METHODS
    // ===================================================================

    /**
     * Generate table versions (content hashes)
     */
    private function generateTableVersions($schoolId)
    {
        $versions = [];
        
        $tables = [
            'users' => User::where('school_id', $schoolId)->get(['id', 'updated_at']),
            'courses' => Course::where('school_id', $schoolId)->get(['id', 'updated_at']),
            'fleet' => Fleet::where('school_id', $schoolId)->get(['id', 'updated_at']),
            'schedules' => Schedule::where('school_id', $schoolId)->get(['id', 'updated_at']),
            'invoices' => Invoice::where('school_id', $schoolId)->get(['id', 'updated_at']),
            'payments' => Payment::whereHas('user', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })->get(['id', 'updated_at']),
        ];

        foreach ($tables as $tableName => $records) {
            // Create a hash based on all record IDs and update timestamps
            $hashData = $records->sortBy('id')->pluck('updated_at', 'id')->toArray();
            $versions[$tableName] = substr(md5(serialize($hashData)), 0, 16);
        }

        return $versions;
    }

    /**
     * Get data counts for each table
     */
    private function getDataCounts($schoolId)
    {
        return [
            'users' => User::where('school_id', $schoolId)->count(),
            'courses' => Course::where('school_id', $schoolId)->count(),
            'fleet' => Fleet::where('school_id', $schoolId)->count(),
            'schedules' => Schedule::where('school_id', $schoolId)->count(),
            'invoices' => Invoice::where('school_id', $schoolId)->count(),
            'payments' => Payment::whereHas('user', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })->count(),
        ];
    }

    /**
     * Get or create device information
     */
    private function getOrCreateDeviceInfo($schoolId, $deviceId)
    {
        if (!$deviceId) {
            return null;
        }

        return DeviceRegistration::firstOrCreate(
            [
                'school_id' => $schoolId,
                'device_id' => $deviceId,
            ],
            [
                'user_id' => auth()->id(),
                'platform' => 'unknown',
                'status' => 'active',
                'last_seen' => now(),
            ]
        );
    }

    /**
     * Check if device needs full sync
     */
    private function checkIfDeviceRequiresFullSync($deviceInfo)
    {
        if (!$deviceInfo) {
            return true; // New device always needs full sync
        }

        // Check if device hasn't synced in a long time
        if ($deviceInfo->last_successful_sync && 
            $deviceInfo->last_successful_sync->diffInDays(now()) > 7) {
            return true;
        }

        // Check if device is marked for full sync
        if ($deviceInfo->requires_full_sync) {
            return true;
        }

        return false;
    }

    /**
     * Process individual upload item
     */
    private function processUploadItem($type, $item)
    {
        try {
            $operation = $item['operation'] ?? 'upsert';
            $data = $item['data'] ?? [];

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
            return false;
        }
    }

    /**
     * Upsert user record
     */
    private function upsertUser($data, $operation)
    {
        if ($operation === 'delete') {
            User::where('id', $data['id'])
                 ->where('school_id', $data['school_id'])
                 ->delete();
            return true;
        }

        // Validate required fields
        if (!isset($data['email'])) {
            throw new \Exception('Email is required for user operations');
        }

        User::updateOrCreate(
            ['id' => $data['id']],
            [
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
                // Don't update password from sync for security
            ]
        );

        return true;
    }

    /**
     * Upsert course record
     */
    private function upsertCourse($data, $operation)
    {
        if ($operation === 'delete') {
            Course::where('id', $data['id'])
                  ->where('school_id', $data['school_id'])
                  ->delete();
            return true;
        }

        if (!isset($data['name'])) {
            throw new \Exception('Name is required for course operations');
        }

        Course::updateOrCreate(
            ['id' => $data['id']],
            [
                'school_id' => $data['school_id'],
                'name' => $data['name'],
                'price' => $data['price'] ?? 0,
                'status' => $data['status'] ?? 'active',
                'duration_hours' => $data['duration_hours'] ?? 0,
                'description' => $data['description'] ?? '',
            ]
        );

        return true;
    }

    /**
     * Upsert fleet record
     */
    private function upsertFleet($data, $operation)
    {
        if ($operation === 'delete') {
            Fleet::where('id', $data['id'])
                 ->where('school_id', $data['school_id'])
                 ->delete();
            return true;
        }

        if (!isset($data['make']) || !isset($data['model'])) {
            throw new \Exception('Make and model are required for fleet operations');
        }

        Fleet::updateOrCreate(
            ['id' => $data['id']],
            [
                'school_id' => $data['school_id'],
                'make' => $data['make'],
                'model' => $data['model'],
                'modelyear' => $data['modelyear'] ?? $data['model_year'] ?? date('Y'),
                'carplate' => $data['carplate'] ?? $data['carPlate'] ?? '',
                'status' => $data['status'] ?? 'available',
                'instructor' => $data['instructor'] ?? null,
            ]
        );

        return true;
    }

    /**
     * Upsert schedule record
     */
    private function upsertSchedule($data, $operation)
    {
        if ($operation === 'delete') {
            Schedule::where('id', $data['id'])
                    ->where('school_id', $data['school_id'])
                    ->delete();
            return true;
        }

        if (!isset($data['start']) || !isset($data['end'])) {
            throw new \Exception('Start and end times are required for schedule operations');
        }

        Schedule::updateOrCreate(
            ['id' => $data['id']],
            [
                'school_id' => $data['school_id'],
                'student' => $data['student_id'] ?? $data['student'] ?? null,
                'instructor' => $data['instructor_id'] ?? $data['instructor'] ?? null,
                'course' => $data['course_id'] ?? $data['course'] ?? null,
                'car' => $data['vehicle_id'] ?? $data['car'] ?? null,
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

        return true;
    }

    /**
     * Upsert invoice record
     */
    private function upsertInvoice($data, $operation)
    {
        if ($operation === 'delete') {
            Invoice::where('id', $data['id'])
                   ->where('school_id', $data['school_id'])
                   ->delete();
            return true;
        }

        if (!isset($data['student_id']) || !isset($data['total_amount'])) {
            throw new \Exception('Student ID and total amount are required for invoice operations');
        }

        Invoice::updateOrCreate(
            ['id' => $data['id']],
            [
                'school_id' => $data['school_id'],
                'student_id' => $data['student_id'],
                'course_id' => $data['course_id'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? 'INV-' . time(),
                'total_amount' => $data['total_amount'],
                'paid_amount' => $data['paid_amount'] ?? 0,
                'status' => $data['status'] ?? 'pending',
                'due_date' => $data['due_date'] ?? now()->addDays(30)->toDateString(),
                'notes' => $data['notes'] ?? '',
            ]
        );

        return true;
    }

    /**
     * Upsert payment record
     */
    private function upsertPayment($data, $operation)
    {
        if ($operation === 'delete') {
            Payment::where('id', $data['id'])
                   ->whereHas('user', function($query) use ($data) {
                       $query->where('school_id', $data['school_id']);
                   })
                   ->delete();
            return true;
        }

        if (!isset($data['amount']) || !isset($data['userId'])) {
            throw new \Exception('Amount and user ID are required for payment operations');
        }

        Payment::updateOrCreate(
            ['id' => $data['id']],
            [
                'invoiceId' => $data['invoiceId'] ?? $data['invoice_id'] ?? null,
                'amount' => $data['amount'],
                'method' => $data['method'] ?? 'cash',
                'paymentDate' => $data['paymentDate'] ?? $data['payment_date'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'completed',
                'notes' => $data['notes'] ?? '',
                'reference' => $data['reference'] ?? null,
                'receipt_path' => $data['receipt_path'] ?? null,
                'receipt_generated' => $data['receipt_generated'] ?? false,
                'userId' => $data['userId'] ?? $data['user_id'],
            ]
        );

        return true;
    }

    /**
     * Get sync status with detailed information
     */
    public function getSyncStatus(Request $request)
    {
        try {
            $currentUser = auth()->user();
            $schoolId = $currentUser->school_id;

            if (!$schoolId) {
                return $this->sendError('User does not belong to any school.', [], 400);
            }

            $stats = [
                'school_id' => $schoolId,
                'data_counts' => $this->getDataCounts($schoolId),
                'table_versions' => $this->generateTableVersions($schoolId),
                'last_updated' => $this->getLastUpdatedTimes($schoolId),
                'active_devices' => DeviceRegistration::where('school_id', $schoolId)
                    ->where('status', 'active')
                    ->count(),
                'server_time' => now()->toISOString(),
            ];

            return $this->sendResponse($stats, 'Sync status retrieved successfully.');

        } catch (\Exception $e) {
            Log::error('Error getting sync status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Failed to get sync status.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get last updated times for each table
     */
    private function getLastUpdatedTimes($schoolId)
    {
        return [
            'users' => User::where('school_id', $schoolId)->max('updated_at'),
            'courses' => Course::where('school_id', $schoolId)->max('updated_at'),
            'fleet' => Fleet::where('school_id', $schoolId)->max('updated_at'),
            'schedules' => Schedule::where('school_id', $schoolId)->max('updated_at'),
            'invoices' => Invoice::where('school_id', $schoolId)->max('updated_at'),
            'payments' => Payment::whereHas('user', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })->max('updated_at'),
        ];
    }

    /**
     * Health check endpoint
     */
    public function health()
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'server' => 'Laravel Production Sync API',
            'version' => '1.0.0'
        ]);
    }
}
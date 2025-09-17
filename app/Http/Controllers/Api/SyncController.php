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
                    
                'schedules' => Schedule::with(['student', 'instructor', 'course', 'vehicle'])
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

            // Process data types in dependency order
            $dataTypes = ['users', 'courses', 'fleet', 'invoices', 'schedules', 'payments'];
            
            foreach ($dataTypes as $type) {
                if ($request->has($type)) {
                    $result = $this->processDataType($type, $request->$type, $schoolId);
                    $uploaded += $result['uploaded'];
                    if (!empty($result['errors'])) {
                        $errors[$type] = $result['errors'];
                    }
                }
            }

            if (empty($errors)) {
                DB::commit();
                return $this->sendResponse([
                    'uploaded' => $uploaded,
                    'timestamp' => now()->toISOString()
                ], 'Data uploaded successfully.');
            } else {
                DB::commit(); // Still commit partial successes
                return $this->sendError('Upload partially failed.', [
                    'uploaded' => $uploaded,
                    'errors' => $errors
                ], 422);
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Sync upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('Upload failed.', ['error' => $e->getMessage()], 500);
        }
    }

    private function processDataType($type, $data, $schoolId)
    {
        $uploaded = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                Log::info("Processing {$type} item", ['item' => $item]);

                $actualData = $item['data'] ?? $item;
                $operation = $item['operation'] ?? 'create';

                // ✅ ENSURE SCHOOL_ID IS SET FOR ALL DATA
                $actualData['school_id'] = $schoolId;

                Log::info("Extracted data for {$type}", [
                    'operation' => $operation, 
                    'school_id' => $schoolId,
                    'data' => $actualData
                ]);

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
                }

                $uploaded++;

            } catch (\Exception $e) {
                $errors[] = [
                    'item' => $item,
                    'error' => $e->getMessage()
                ];
                Log::error("Failed to process {$type} item", [
                    'item' => $item,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'uploaded' => $uploaded,
            'errors' => $errors
        ];
    }

    private function upsertUser($data, $operation)
    {
        if ($operation === 'delete') {
            User::where('id', $data['id'])->delete();
            return;
        }

        User::updateOrCreate(
            ['id' => $data['id']],
            [
                'fname' => $data['fname'] ?? '',
                'lname' => $data['lname'] ?? '',
                'email' => $data['email'],
                'role' => $data['role'],
                'phone' => $data['phone'] ?? '',
                'status' => $data['status'] ?? 'active',
                'school_id' => $data['school_id'], // ✅ INCLUDE SCHOOL_ID
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
            Course::where('id', $data['id'])->delete();
            return;
        }

        Course::updateOrCreate(
            ['id' => $data['id']],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'duration_hours' => $data['duration_hours'] ?? 0,
                'price' => $data['price'] ?? 0,
                'status' => $data['status'] ?? 'active',
                'school_id' => $data['school_id'], // ✅ INCLUDE SCHOOL_ID
            ]
        );
    }

    private function upsertFleet($data, $operation)
    {
        if ($operation === 'delete') {
            Fleet::where('id', $data['id'])->delete();
            return;
        }

        // ✅ CORRECTED: Map client fields to correct database fields
        Fleet::updateOrCreate(
            ['id' => $data['id']],
            [
                'make' => $data['make'],
                'model' => $data['model'],
                'modelyear' => $data['modelyear'] ?? $data['year'] ?? date('Y'), // ✅ Fixed field name
                'carplate' => $data['carplate'] ?? $data['carPlate'] ?? '', // ✅ Fixed field name
                'status' => $data['status'] ?? 'available',
                'instructor' => $data['instructor'], // ✅ This was already correct
                'school_id' => $data['school_id'],
                // ❌ REMOVED: transmission and fuel_type (these fields don't exist in the schema)
            ]
        );
    }

    private function upsertSchedule($data, $operation)
    {
        if ($operation === 'delete') {
            Schedule::where('id', $data['id'])->delete();
            return;
        }

        Schedule::updateOrCreate(
            ['id' => $data['id']],
            [
                'student' => $data['student_id'] ?? $data['student'],
                'instructor' => $data['instructor_id'] ?? $data['instructor'],
                'course' => $data['course_id'] ?? $data['course'],
                'vehicle' => $data['vehicle_id'] ?? $data['car'],
                'start' => $data['start'],
                'end' => $data['end'],
                'status' => $data['status'] ?? 'scheduled',
                'class_type' => $data['class_type'] ?? 'Practical',
                'notes' => $data['notes'] ?? '',
                'school_id' => $data['school_id'], // ✅ INCLUDE SCHOOL_ID
            ]
        );
    }

    private function upsertInvoice($data, $operation)
    {
        if ($operation === 'delete') {
            Invoice::where('id', $data['id'])->delete();
            return;
        }

        Invoice::updateOrCreate(
            ['id' => $data['id']],
            [
                'student' => $data['student_id'] ?? $data['student'],
                'course' => $data['course_id'] ?? $data['course'],
                'total_amount' => $data['total_amount'] ?? $data['amount'],
                'status' => $data['status'] ?? 'pending',
                'due_date' => $data['due_date'] ?? null,
                'school_id' => $data['school_id'], // ✅ INCLUDE SCHOOL_ID
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
                'invoice_id' => $data['invoice_id'],
                'student_id' => $data['student_id'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
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
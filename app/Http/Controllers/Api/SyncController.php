<?php
// app/Http/Controllers/Api/SyncController.php - COMPLETE FIXED VERSION

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
            $lastSync = $request->header('Last-Sync')
                ? Carbon::parse($request->header('Last-Sync'))
                : Carbon::now()->subYears(10); // Get all data if no last sync

            $data = [
                'users' => User::where('updated_at', '>', $lastSync)->get(),
                'courses' => Course::where('updated_at', '>', $lastSync)->get(),
                'fleet' => Fleet::where('updated_at', '>', $lastSync)->get(),
                'schedules' => Schedule::with(['student', 'instructor', 'course', 'vehicle'])
                    ->where('updated_at', '>', $lastSync)->get(),
                'invoices' => Invoice::with(['student', 'course', 'payments'])
                    ->where('updated_at', '>', $lastSync)->get(),
                'payments' => Payment::with(['invoice', 'student'])
                    ->where('updated_at', '>', $lastSync)->get(),
                'sync_timestamp' => now()->toISOString(),
            ];

            return $this->sendResponse($data, 'Data synchronized successfully.');

        } catch (\Exception $e) {
            return $this->sendError('Sync failed.', ['error' => $e->getMessage()], 500);
        }
    }

    public function upload(Request $request)
    {
        DB::beginTransaction();

        try {
            $uploaded = 0;
            $errors = [];

            // Process data types in dependency order (invoices before payments)
            $dataTypes = ['users', 'courses', 'fleet', 'invoices', 'schedules', 'payments'];
            
            foreach ($dataTypes as $type) {
                if ($request->has($type)) {
                    $result = $this->processDataType($type, $request->$type);
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
                // Only rollback if there are critical errors
                // For now, let's still commit partial successes
                DB::commit();
                return $this->sendError('Upload partially failed.', [
                    'uploaded' => $uploaded,
                    'errors' => $errors
                ], 422);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Upload failed.', ['error' => $e->getMessage()], 500);
        }
    }

    private function processDataType($type, $data)
    {
        $uploaded = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                // Log what we're processing for debugging
                Log::info("Processing {$type} item", ['item' => $item]);

                // Extract the actual data from the sync item structure
                $actualData = $item['data'] ?? $item;
                $operation = $item['operation'] ?? 'create';

                Log::info("Extracted data for {$type}", [
                    'operation' => $operation, 
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
                Log::error("Error processing {$type} item", [
                    'item' => $item,
                    'error' => $e->getMessage()
                ]);
                
                $errors[] = [
                    'item' => $item,
                    'error' => $e->getMessage()
                ];
            }
        }

        return ['uploaded' => $uploaded, 'errors' => $errors];
    }

    private function upsertUser($data, $operation = 'create')
    {
        $cleanData = collect($data)->except(['id'])->toArray();
        
        if ($operation === 'delete') {
            User::where('id', $data['id'])->delete();
            return;
        }
        
        User::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $cleanData
        );
    }

    private function upsertCourse($data, $operation = 'create')
    {
        $cleanData = collect($data)->except(['id'])->toArray();
        
        if ($operation === 'delete') {
            Course::where('id', $data['id'])->delete();
            return;
        }
        
        Log::info('Upserting course', ['id' => $data['id'] ?? null, 'data' => $cleanData]);
        
        Course::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $cleanData
        );
    }

    private function upsertFleet($data, $operation = 'create')
    {
        $cleanData = collect($data)->except(['id'])->toArray();
        
        if ($operation === 'delete') {
            Fleet::where('id', $data['id'])->delete();
            return;
        }
        
        // Handle instructor field - convert 0 to NULL
        if (isset($cleanData['instructor']) && $cleanData['instructor'] == 0) {
            $cleanData['instructor'] = null;
        }
        
        // Validate that instructor exists if provided
        if (!empty($cleanData['instructor'])) {
            $instructorExists = User::where('id', $cleanData['instructor'])->exists();
            if (!$instructorExists) {
                throw new \Exception("Instructor with ID {$cleanData['instructor']} does not exist");
            }
        }
        
        Log::info('Upserting fleet', ['id' => $data['id'] ?? null, 'data' => $cleanData]);
        
        Fleet::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $cleanData
        );
    }

    private function upsertSchedule($data, $operation = 'create')
    {
        $cleanData = collect($data)->except(['id', 'student', 'instructor', 'course', 'vehicle'])->toArray();
        
        if ($operation === 'delete') {
            Schedule::where('id', $data['id'])->delete();
            return;
        }
        
        Schedule::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $cleanData
        );
    }

    private function upsertInvoice($data, $operation = 'create')
    {
        Log::info('Processing invoice', ['data' => $data, 'operation' => $operation]);
        
        if ($operation === 'delete') {
            Invoice::where('id', $data['id'])->delete();
            return;
        }
        
        // For UPDATE operations, we need to handle partial data
        if ($operation === 'update') {
            // Get existing invoice
            $existingInvoice = Invoice::find($data['id']);
            if (!$existingInvoice) {
                throw new \Exception("Cannot update invoice {$data['id']}: not found");
            }
            
            // Only update the fields that are provided
            $updateFields = collect($data)->except(['id'])->toArray();
            
            Log::info('Updating existing invoice', [
                'id' => $data['id'], 
                'updateFields' => $updateFields
            ]);
            
            $existingInvoice->update($updateFields);
            return;
        }
        
        // For CREATE operations, ensure all required fields are present
        $cleanData = collect($data)->except(['id'])->toArray();
        
        // Validate required fields for creation
        if (!isset($cleanData['student'])) {
            throw new \Exception("Required field 'student' missing for invoice creation");
        }
        
        Log::info('Creating new invoice', ['data' => $cleanData]);
        
        Invoice::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $cleanData
        );
    }

    private function upsertPayment($data, $operation = 'create')
    {
        Log::info('Processing payment', ['data' => $data, 'operation' => $operation]);
        
        if ($operation === 'delete') {
            Payment::where('id', $data['id'])->delete();
            return;
        }
        
        // For UPDATE operations, handle partial data
        if ($operation === 'update') {
            $existingPayment = Payment::find($data['id']);
            if (!$existingPayment) {
                throw new \Exception("Cannot update payment {$data['id']}: not found");
            }
            
            $updateFields = collect($data)->except(['id'])->toArray();
            
            Log::info('Updating existing payment', [
                'id' => $data['id'], 
                'updateFields' => $updateFields
            ]);
            
            $existingPayment->update($updateFields);
            return;
        }
        
        // For CREATE operations, ensure invoice exists and required fields are present
        $cleanData = collect($data)->except(['id', 'invoice', 'student'])->toArray();
        
        // Validate that invoice exists
        if (isset($cleanData['invoiceId'])) {
            $invoiceExists = Invoice::where('id', $cleanData['invoiceId'])->exists();
            if (!$invoiceExists) {
                throw new \Exception("Invoice with ID {$cleanData['invoiceId']} does not exist");
            }
        } else {
            throw new \Exception("Required field 'invoiceId' missing for payment creation");
        }
        
        // Validate that user exists if provided
        if (isset($cleanData['userId']) && !empty($cleanData['userId'])) {
            $userExists = User::where('id', $cleanData['userId'])->exists();
            if (!$userExists) {
                throw new \Exception("User with ID {$cleanData['userId']} does not exist");
            }
        }
        
        Log::info('Creating new payment', ['data' => $cleanData]);
        
        Payment::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $cleanData
        );
    }

    public function status()
    {
        $stats = [
            'users' => User::count(),
            'courses' => Course::count(),
            'fleet' => Fleet::count(),
            'schedules' => Schedule::count(),
            'invoices' => Invoice::count(),
            'payments' => Payment::count(),
            'last_activity' => [
                'users' => User::latest('updated_at')->first()?->updated_at,
                'schedules' => Schedule::latest('updated_at')->first()?->updated_at,
                'payments' => Payment::latest('updated_at')->first()?->updated_at,
            ],
            'server_time' => now()->toISOString(),
        ];

        return $this->sendResponse($stats, 'Sync status retrieved successfully.');
    }
}
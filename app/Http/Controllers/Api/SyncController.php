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


   private function upsertInvoice($data, $operation = 'create')
{
    Log::info('Processing invoice', ['data' => $data, 'operation' => $operation]);
    
    if ($operation === 'delete') {
        Invoice::where('id', $data['id'])->delete();
        return;
    }
    
    if ($operation === 'update') {
        // For UPDATE: Only update existing records, don't create new ones
        $updated = Invoice::where('id', $data['id'])
            ->update(collect($data)->except(['id'])->toArray());
        
        if ($updated === 0) {
            Log::warning("Invoice {$data['id']} not found for update - will attempt upsert");
            // If record doesn't exist, we need all required fields to create it
            // But since this is an UPDATE operation, we probably don't have them
            // So we'll skip this operation
            throw new \Exception("Cannot update invoice {$data['id']}: not found on server");
        }
        
        Log::info("Successfully updated invoice {$data['id']}");
        return;
    }
    
    // For CREATE operations
    $cleanData = collect($data)->except(['id'])->toArray();
    
    // Validate required fields for creation
    if (!isset($cleanData['student'])) {
        throw new \Exception("Required field 'student' missing for invoice creation");
    }
    
    Log::info('Creating new invoice', ['data' => $cleanData]);
    
    // For CREATE, use updateOrCreate to handle potential ID conflicts
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
    
    if ($operation === 'update') {
        // For UPDATE: Only update existing records, don't create new ones
        $updated = Payment::where('id', $data['id'])
            ->update(collect($data)->except(['id', 'invoice', 'student'])->toArray());
        
        if ($updated === 0) {
            Log::warning("Payment {$data['id']} not found for update");
            throw new \Exception("Cannot update payment {$data['id']}: not found on server");
        }
        
        Log::info("Successfully updated payment {$data['id']}");
        return;
    }
    
    // For CREATE operations
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

// Also fix the other upsert methods for consistency
private function upsertUser($data, $operation = 'create')
{
    if ($operation === 'delete') {
        User::where('id', $data['id'])->delete();
        return;
    }
    
    $cleanData = collect($data)->except(['id'])->toArray();
    
    if ($operation === 'update') {
        $updated = User::where('id', $data['id'])->update($cleanData);
        if ($updated === 0) {
            // For users, we might want to create if not exists
            User::create(array_merge($cleanData, ['id' => $data['id']]));
        }
        return;
    }
    
    // CREATE operation
    User::updateOrCreate(
        ['id' => $data['id'] ?? null],
        $cleanData
    );
}

private function upsertCourse($data, $operation = 'create')
{
    if ($operation === 'delete') {
        Course::where('id', $data['id'])->delete();
        return;
    }
    
    $cleanData = collect($data)->except(['id'])->toArray();
    
    if ($operation === 'update') {
        $updated = Course::where('id', $data['id'])->update($cleanData);
        if ($updated === 0) {
            Course::create(array_merge($cleanData, ['id' => $data['id']]));
        }
        return;
    }
    
    Course::updateOrCreate(
        ['id' => $data['id'] ?? null],
        $cleanData
    );
}

private function upsertFleet($data, $operation = 'create')
{
    if ($operation === 'delete') {
        Fleet::where('id', $data['id'])->delete();
        return;
    }
    
    $cleanData = collect($data)->except(['id'])->toArray();
    
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
    
    if ($operation === 'update') {
        $updated = Fleet::where('id', $data['id'])->update($cleanData);
        if ($updated === 0) {
            Fleet::create(array_merge($cleanData, ['id' => $data['id']]));
        }
        return;
    }
    
    Fleet::updateOrCreate(
        ['id' => $data['id'] ?? null],
        $cleanData
    );
}

private function upsertSchedule($data, $operation = 'create')
{
    if ($operation === 'delete') {
        Schedule::where('id', $data['id'])->delete();
        return;
    }
    
    $cleanData = collect($data)->except(['id', 'student', 'instructor', 'course', 'vehicle'])->toArray();
    
    if ($operation === 'update') {
        $updated = Schedule::where('id', $data['id'])->update($cleanData);
        if ($updated === 0) {
            Schedule::create(array_merge($cleanData, ['id' => $data['id']]));
        }
        return;
    }
    
    Schedule::updateOrCreate(
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
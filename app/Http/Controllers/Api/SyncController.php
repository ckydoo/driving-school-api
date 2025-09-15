<?php
// app/Http/Controllers/Api/SyncController.php

namespace App\Http\Controllers\Api;

use App\Models\{User, Course, Fleet, Schedule, Invoice, Payment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            // Process each data type
            foreach (['users', 'courses', 'fleet', 'schedules', 'invoices', 'payments'] as $type) {
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
                DB::rollback();
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
                switch ($type) {
                    case 'users':
                        $this->upsertUser($item);
                        break;
                    case 'courses':
                        $this->upsertCourse($item);
                        break;
                    case 'fleet':
                        $this->upsertFleet($item);
                        break;
                    case 'schedules':
                        $this->upsertSchedule($item);
                        break;
                    case 'invoices':
                        $this->upsertInvoice($item);
                        break;
                    case 'payments':
                        $this->upsertPayment($item);
                        break;
                }
                $uploaded++;
            } catch (\Exception $e) {
                $errors[] = [
                    'item' => $item,
                    'error' => $e->getMessage()
                ];
            }
        }

        return ['uploaded' => $uploaded, 'errors' => $errors];
    }

    private function upsertUser($data)
    {
        User::updateOrCreate(
            ['id' => $data['id'] ?? null],
            collect($data)->except(['id'])->toArray()
        );
    }

    private function upsertCourse($data)
    {
        Course::updateOrCreate(
            ['id' => $data['id'] ?? null],
            collect($data)->except(['id'])->toArray()
        );
    }

    private function upsertFleet($data)
    {
        Fleet::updateOrCreate(
            ['id' => $data['id'] ?? null],
            collect($data)->except(['id'])->toArray()
        );
    }

    private function upsertSchedule($data)
    {
        Schedule::updateOrCreate(
            ['id' => $data['id'] ?? null],
            collect($data)->except(['id', 'student', 'instructor', 'course', 'vehicle'])->toArray()
        );
    }

    private function upsertInvoice($data)
    {
        Invoice::updateOrCreate(
            ['id' => $data['id'] ?? null],
            collect($data)->except(['id', 'student', 'course', 'payments'])->toArray()
        );
    }

    private function upsertPayment($data)
    {
        Payment::updateOrCreate(
            ['id' => $data['id'] ?? null],
            collect($data)->except(['id', 'invoice', 'student'])->toArray()
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

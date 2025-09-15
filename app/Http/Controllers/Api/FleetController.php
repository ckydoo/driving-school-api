<?php
// app/Http/Controllers/Api/FleetController.php

namespace App\Http\Controllers\Api;

use App\Models\Fleet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FleetController extends BaseController
{
    public function index(Request $request)
    {
        $query = Fleet::with('assignedInstructor');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }


        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('carplate', 'like', "%{$search}%");
            });
        }

        $fleet = $query->orderBy('make', 'asc')->orderBy('model', 'asc')->get();

        return $this->sendResponse($fleet, 'Fleet retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'carplate' => 'required|string|max:255|unique:fleet',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'assigned_instructor_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $vehicle = Fleet::create($request->all());
        $vehicle->load('assignedInstructor');

        return $this->sendResponse($vehicle, 'Vehicle created successfully.');
    }

    public function show($id)
    {
        $vehicle = Fleet::with(['assignedInstructor', 'schedules'])->find($id);

        if (is_null($vehicle)) {
            return $this->sendError('Vehicle not found.');
        }

        return $this->sendResponse($vehicle, 'Vehicle retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $vehicle = Fleet::find($id);

        if (is_null($vehicle)) {
            return $this->sendError('Vehicle not found.');
        }

        $validator = Validator::make($request->all(), [
            'make' => 'sometimes|required|string|max:255',
            'model' => 'sometimes|required|string|max:255',
            'carplate' => 'sometimes|required|string|max:255|unique:fleet,carplate,' . $id,
            'year' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'sometimes|required|in:available,in_use,maintenance,out_of_service',
            'assigned_instructor_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $vehicle->update($request->only([
            'make', 'model', 'carplate', 'year', 'status',
            'assigned_instructor_id', 'notes'
        ]));

        $vehicle->load('assignedInstructor');

        return $this->sendResponse($vehicle, 'Vehicle updated successfully.');
    }

    public function destroy($id)
    {
        $vehicle = Fleet::find($id);

        if (is_null($vehicle)) {
            return $this->sendError('Vehicle not found.');
        }

        // Check if vehicle is being used
        if ($vehicle->schedules()->count() > 0) {
            return $this->sendError('Cannot delete vehicle that is being used in schedules.');
        }

        $vehicle->delete();

        return $this->sendResponse([], 'Vehicle deleted successfully.');
    }

    public function available()
    {
        $vehicles = Fleet::available()->get();
        return $this->sendResponse($vehicles, 'Available vehicles retrieved successfully.');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * عرض كل الخدمات (GET /api/services).
     */
    public function index(Request $request)
    {
        $query = Resource::where('type', 'service');
        
        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $services = $query->paginate(10);
        
        return response()->json($services);
    }

    /**
     * إنشاء خدمة جديدة (POST /api/services).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:resources',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $service = Resource::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => 'service', // دائماً service
            'capacity' => $request->capacity,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Service created successfully',
            'service' => $service
        ], 201);
    }

    /**
     * عرض خدمة معينة (GET /api/services/{serviceId}).
     */
    public function show($id)
    {
        $service = Resource::where('type', 'service')->findOrFail($id);
        
        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        $service = Resource::where('type', 'service')->findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:resources,name,' . $service->id,
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Only update fields that are present in the request
        $updateData = array_filter([
            'name' => $request->name,
            'description' => $request->description,
            'capacity' => $request->capacity,
            'is_active' => $request->is_active,
        ], function ($value) {
            return $value !== null;
        });

        $service->update($updateData);

        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service
        ]);
    }

    /**
     * حذف خدمة (DELETE /api/services/{serviceId}).
     */
    public function destroy($id)
    {
        $service = Resource::where('type', 'service')->findOrFail($id);
        
        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully'
        ], 204);
    }
}

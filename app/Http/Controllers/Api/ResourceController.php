<?php

// App/Http/Controllers/Api/ResourceController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * عرض قائمة بكل الموارد (GET /api/resources).
     */
    public function index()
    {
        // استخدام paginate للحصول على قائمة مقسمة (أفضل للمشاريع الكبيرة)
        return response()->json(Resource::paginate(10));
    }

    /**
     * إنشاء مورد جديد (POST /api/resources).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:resources',
            'description' => 'nullable|string',
            'type' => 'required|in:hall,clinic,service',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $resource = Resource::create($request->all());

        return response()->json([
            'message' => 'Resource created successfully',
            'resource' => $resource
        ], 201);
    }

    /**
     * عرض مورد واحد بالتفصيل (GET /api/resources/{resource}).
     */
    public function show(Resource $resource)
    {
        return response()->json($resource);
    }

    /**
     * تحديث مورد موجود (PUT/PATCH /api/resources/{resource}).
     */
    public function update(Request $request, Resource $resource)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:resources,name,' . $resource->id,
            'description' => 'nullable|string',
            'type' => 'sometimes|in:hall,clinic,service',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $resource->update($request->all());

        return response()->json([
            'message' => 'Resource updated successfully',
            'resource' => $resource
        ]);
    }

    /**
     * حذف مورد (DELETE /api/resources/{resource}).
     */
    public function destroy(Resource $resource)
    {
        $resource->delete();

        return response()->json([
            'message' => 'Resource deleted successfully'
        ], 204); // 204 No Content
    }
}
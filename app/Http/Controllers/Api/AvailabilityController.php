<?php

// App/Http/Controllers/Api/AvailabilityController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\Resource;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * عرض قائمة بجميع أوقات التوافر (GET /api/availabilities).
     */
    public function index()
    {
        // عرض التوافر مع اسم المورد المرتبط به
        return response()->json(
            Availability::with('resource:id,name')->paginate(10)
        );
    }

    /**
     * إنشاء وقت توافر جديد (POST /api/availabilities).
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'resource_id' => 'required|exists:resources,id',
            // يوم الأسبوع (1 = الاثنين، 7 = الأحد)
            'day_of_week' => 'required|integer|between:1,7', 
            'start_time' => 'required|date_format:H:i:s', // مثلاً 09:00:00
            'end_time' => 'required|date_format:H:i:s|after:start_time', // يجب أن يكون بعد وقت البدء
            'date_from' => 'nullable|date|date_format:Y-m-d',
            'date_to' => 'nullable|date|date_format:Y-m-d|after_or_equal:date_from',
        ]);

        $availability = Availability::create($request->all());

        return response()->json([
            'message' => 'Availability schedule created successfully',
            'availability' => $availability
        ], 201);
    }
    
    /**
     * تحديث وقت توافر موجود (PUT/PATCH /api/availabilities/{availability}).
     */
    public function update(Request $request, Availability $availability)
    {
        // التحقق من صحة البيانات (باستخدام sometimes لأن الحقول اختيارية في التحديث)
        $request->validate([
            'resource_id' => 'sometimes|exists:resources,id',
            'day_of_week' => 'sometimes|integer|between:1,7',
            'start_time' => 'sometimes|date_format:H:i:s',
            'end_time' => 'sometimes|date_format:H:i:s|after:start_time',
            'date_from' => 'nullable|date|date_format:Y-m-d',
            'date_to' => 'nullable|date|date_format:Y-m-d|after_or_equal:date_from',
        ]);
        
        // يجب أن نتحقق بشكل يدوي من شرط after:start_time في حال تحديث end_time فقط
        if ($request->has('start_time') && $request->has('end_time')) {
            // تم التحقق في الـ Validation أعلاه
        } elseif ($request->has('start_time') && strtotime($request->start_time) >= strtotime($availability->end_time)) {
             return response()->json(['message' => 'Start time must be before end time.'], 422);
        } elseif ($request->has('end_time') && strtotime($request->end_time) <= strtotime($availability->start_time)) {
             return response()->json(['message' => 'End time must be after start time.'], 422);
        }

        $availability->update($request->all());

        return response()->json([
            'message' => 'Availability schedule updated successfully',
            'availability' => $availability
        ]);
    }

    /**
     * حذف وقت توافر (DELETE /api/availabilities/{availability}).
     */
    public function destroy(Availability $availability)
    {
        $availability->delete();

        return response()->json([
            'message' => 'Availability schedule deleted successfully'
        ], 204);
    }
}

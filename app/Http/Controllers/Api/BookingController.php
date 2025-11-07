<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Resource;
use App\Models\Availability; // تأكيد الاستيراد
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Events\BookingCreated;
use App\Events\BookingCancelled;
use App\Http\Resources\BookingResource; // افتراض وجود هذا المسار
use Illuminate\Http\Resources\Json\JsonResource; // يجب أن يكون موجودًا أيضًا إذا كان Resource في نفس الملف (تم حذفه للتبسيط)

class BookingController extends Controller
{
    /**
     * يحسب ويُرجع فترات الحجز المتاحة لمورد معين خلال نطاق تاريخي.
     * GET /api/available-slots
     */
    public function getAvailableSlots(Request $request)
    {
        // 1. Validation
        $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            // مدة الحجز المطلوبة (بالدقائق)
            'duration_minutes' => 'required|integer|min:5|max:1440',
        ]);

        $resourceId = $request->resource_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $duration = (int) $request->duration_minutes;
        // فترة التنقل الافتراضية للعميل (مثلاً يعرض كل نص ساعة)
        $slotIncrement = 30;

        $finalAvailableSlots = [];

        // 2. Loop through each day in the requested range
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

            $dayOfWeek = $date->dayOfWeekIso; // 1 (الاثنين) إلى 7 (الأحد)
            $dateString = $date->toDateString();

            // 2.1. جلب فترات التوافر (Availability) لهذا اليوم من الأسبوع
            // تحقق من وجود المورد أولاً لتجنب استثناء
            $resource = Resource::find($resourceId);
            if (!$resource) {
                continue; // أو إرجاع خطأ 404
            }
            
            $availabilities = $resource
                ->availabilities()
                ->where('day_of_week', $dayOfWeek)
                ->where(function ($query) use ($dateString) {
                    $query->whereNull('date_from') // القواعد الأسبوعية العامة
                        ->orWhere(function ($q) use ($dateString) { // أو القواعد الخاصة بتاريخ محدد
                            $q->whereDate('date_from', '<=', $dateString)
                                ->whereDate('date_to', '>=', $dateString);
                        });
                })
                ->get();

            if ($availabilities->isEmpty()) {
                continue;
            }

            // 2.2. جلب الحجوزات المؤكدة أو قيد الانتظار (Bookings) لهذا اليوم، مرتبة حسب وقت البدء
            $bookings = Booking::where('resource_id', $resourceId)
                ->whereIn('status', ['confirmed', 'pending'])
                ->whereDate('start_time', $dateString)
                ->orderBy('start_time')
                ->get();

            $freeIntervals = [];

            // 3. حساب الفترات الحرة (Free Intervals)
            foreach ($availabilities as $availability) {

                // تحويل أوقات التوافر إلى كائنات Carbon لهذا اليوم
                $periodStart = Carbon::parse($dateString . ' ' . $availability->start_time);
                $periodEnd = Carbon::parse($dateString . ' ' . $availability->end_time);
                $currentTimePointer = $periodStart->copy();

                // تصفية الحجوزات التي تتداخل مع فترة التوافر الحالية
                $relevantBookings = $bookings->filter(function ($b) use ($periodStart, $periodEnd) {
                    // يجب أن يبدأ الحجز قبل انتهاء فترة التوافر وينتهي بعد بدايتها
                    $bookingStart = Carbon::parse($b->start_time);
                    $bookingEnd = Carbon::parse($b->end_time);
                    return $bookingStart->lt($periodEnd) && $bookingEnd->gt($periodStart);
                });
                
                // التأكد من أن مؤشر الوقت يبدأ من بداية فترة التوافر وليس قبلها
                $currentTimePointer = $periodStart->copy();

                foreach ($relevantBookings as $booking) {
                    $bookingStart = Carbon::parse($booking->start_time);
                    $bookingEnd = Carbon::parse($booking->end_time);

                    // إذا كان هناك وقت فارغ بين مؤشر الوقت الحالي وبداية الحجز
                    if ($currentTimePointer->lt($bookingStart)) {
                        $freeIntervals[] = [
                            'start' => $currentTimePointer->toDateTimeString(),
                            'end' => $bookingStart->toDateTimeString(),
                        ];
                    }

                    // تحريك مؤشر الوقت الحالي إلى ما بعد نهاية الحجز (أو ما بعد المؤشر الحالي إن كانت نهاية الحجز أقدم)
                    // يجب أن يكون المؤشر الجديد هو الأكبر بين نهاية الحجز وبداية فترة التوافر (لأن الحجوزات مرتبة)
                    $currentTimePointer = $bookingEnd->copy()->max($currentTimePointer);
                }

                // إذا كان هناك وقت فارغ بعد آخر حجز وحتى نهاية فترة التوافر
                if ($currentTimePointer->lt($periodEnd)) {
                    $freeIntervals[] = [
                        'start' => $currentTimePointer->toDateTimeString(),
                        'end' => $periodEnd->toDateTimeString(),
                    ];
                }
            }
            
            // دمج الفترات الحرة المتجاورة وتصنيفها
            // (هذا الجزء ليس مطلوبًا دائمًا لكنه يحسن الدقة)
            $mergedIntervals = $this->mergeFreeIntervals($freeIntervals);

            // 4. استخراج الأوقات التي تناسب مدة الحجز المطلوبة (Duration)
            $daySlots = [];

            foreach ($mergedIntervals as $interval) {
                $intervalStart = Carbon::parse($interval['start']);
                $intervalEnd = Carbon::parse($interval['end']);

                // البدء في البحث عن Slots من بداية الـ Free Interval
                $slotStartCandidate = $intervalStart->copy();

                // إذا كانت بداية الفترة ليست على مضاعف للـ SlotIncrement، نحتاج إلى تقديم بداية أول Slot لتكون على المضاعف التالي
                $minute = $intervalStart->minute;
                if ($minute % $slotIncrement !== 0) {
                    // نحسب كم دقيقة نحتاجها للوصول للمضاعف التالي
                    $minutesToAdd = $slotIncrement - ($minute % $slotIncrement);
                    $slotStartCandidate->addMinutes($minutesToAdd);
                }

                // Loop لاستخراج جميع الـ Slots الممكنة التي تناسب المدة المطلوبة
                // يجب أن تكون نهاية الـ Slot المرشح أصغر أو تساوي نهاية فترة الـ Interval
                while ($slotStartCandidate->copy()->addMinutes($duration)->lte($intervalEnd)) {

                    $slotEndCandidate = $slotStartCandidate->copy()->addMinutes($duration);

                    $daySlots[] = [
                        'start_time' => $slotStartCandidate->toDateTimeString(),
                        'end_time' => $slotEndCandidate->toDateTimeString(),
                        'duration_minutes' => $duration,
                    ];

                    // الانتقال إلى بداية الـ Slot المحتملة التالية بفترة الـ SlotIncrement (مثلاً 30 دقيقة)
                    $slotStartCandidate->addMinutes($slotIncrement);
                }
            }

            if (!empty($daySlots)) {
                // إزالة التكرارات الناتجة عن تداخل فترات الـ Availability
                $finalAvailableSlots[$dateString] = collect($daySlots)->unique('start_time')->values()->all();
            }
        }

        return response()->json([
            'message' => 'Available slots calculated successfully.',
            'slots' => $finalAvailableSlots,
        ]);
    }

    /**
     * ينشئ حجزًا جديدًا بعد التحقق من التوافر وعدم التضارب.
     * POST /api/bookings
     */
    public function store(Request $request)
    {
        // استخدام guard() للتحقق من أن المستخدم مسجل الدخول، واستخدام id() لاسترجاع المعرف.
        $userId = auth()->guard('api')->id() ?? auth()->id(); // استخدام أكثر أمانًا إذا كنت تستخدم API guards

        // 1. Validation
        $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'start_time' => 'required|date_format:Y-m-d H:i:s|after_or_equal:now',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
        ]);

        $resourceId = $request->resource_id;
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);

        // 2. التحقق من التضارب (Conflict Check)

        // 2.1. التحقق من أوقات العمل والتوافر (Availability Check)
        if (!$this->isWithinAvailability($resourceId, $startTime, $endTime)) {
            return response()->json([
                'message' => 'The requested time slot is outside the resource\'s defined availability schedule.'
            ], 409); // 409 Conflict
        }

        // 2.2. التحقق من التداخل مع حجوزات أخرى (Overlap Check)
        if ($this->hasOverlap($resourceId, $startTime, $endTime)) {
            return response()->json([
                'message' => 'The requested time slot overlaps with an existing confirmed or pending booking.'
            ], 409); // 409 Conflict
        }

        try {
            // 3. إنشاء الحجز
            $booking = Booking::create([
                'user_id' => $userId,
                'resource_id' => $resourceId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'confirmed', // يمكن تعيينها 'pending' إذا كان هناك دفع أو موافقة يدوية
            ]);

            // 4. إطلاق الحدث لإرسال إيميل التأكيد
            event(new BookingCreated($booking));


            // 5. استخدام BookingResource وإرجاع حالة 201 متوافقة مع الـ Resource
            return (new BookingResource($booking))
                ->response()
                ->setStatusCode(201); // 201 Created for consistent API response using the resource
        } catch (\Exception $e) {
            // سجل الخطأ للمراجعة
            \Log::error('Booking creation or event dispatch failed: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'Booking failed due to a server error. Please check logs.',
                'error_detail' => $e->getMessage() // للتصحيح فقط، يفضل إزالته في الإنتاج
            ], 500);
        }
    }

    /**
     * عرض قائمة بحجوزات المستخدم الحالي (GET /api/bookings).
     */
    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with('resource:id,name') // تحميل اسم المورد
            ->latest()
            ->paginate(10);

        // سنستخدم BookingResource لتحويل البيانات وتنسيقها
        return BookingResource::collection($bookings);
    }

    /**
     * عرض تفاصيل حجز معين (GET /api/bookings/{booking}).
     */
    public function show(Booking $booking)
    {
        // التحقق من أن المستخدم يمتلك هذا الحجز (Policy)
        if (auth()->id() !== $booking->user_id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        return new BookingResource($booking->load('resource'));
    }

    /**
     * تعديل أو إلغاء حجز (PATCH /api/bookings/{booking}).
     */
    public function update(Request $request, Booking $booking)
    {
        // 1. التحقق من الصلاحيات والوقت
        if (auth()->id() !== $booking->user_id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // منع التعديل إذا كان الموعد قد بدأ
        if (Carbon::parse($booking->start_time)->isPast()) {
            return response()->json(['message' => 'Cannot update a booking that has already started.'], 400);
        }

        // 2. Validation
        $request->validate([
            // العميل يمكنه فقط تغيير الحالة إلى 'cancelled'
            'status' => ['required', Rule::in(['cancelled'])], // <--- تم إضافة required
        ]);

        $oldStatus = $booking->status;
        $booking->update($request->only('status'));

        // 3. إطلاق حدث الإلغاء في حال تغيير الحالة
        if ($oldStatus !== 'cancelled' && $booking->status === 'cancelled') {
            event(new BookingCancelled($booking));
        }

        return new BookingResource($booking->load('resource'));
    }

    /**
     * حذف حجز (DELETE /api/bookings/{booking}).
     * ملاحظة: يفضل تغيير الحالة إلى 'cancelled' بدلاً من الحذف الفعلي.
     */
    public function destroy(Booking $booking)
    {
        // التحقق من أن المستخدم يمتلك هذا الحجز
        if (auth()->id() !== $booking->user_id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // هنا يمكننا إما حذف الحجز نهائياً أو تغيير حالته إلى 'cancelled' (الأخير هو الأفضل)
        if (Carbon::parse($booking->start_time)->isPast()) {
            return response()->json(['message' => 'Cannot delete a booking that has already started.'], 400);
        }

        // بما أننا نستخدم "update" للإلغاء، يمكن جعل "destroy" للمشرفين فقط
        return response()->json(['message' => 'Use PATCH to change status to cancelled.'], 400);
    }

    // --- HEPLER FUNCTIONS ---

    /**
     * [HELPER] تتحقق من عدم تداخل فترة الحجز المطلوبة مع أي حجوزات أخرى لنفس المورد.
     */
    protected function hasOverlap(int $resourceId, Carbon $startTime, Carbon $endTime): bool
    {
        return Booking::where('resource_id', $resourceId)
            // البحث عن أي حجز حالي يتداخل مع الفترة المطلوبة
            ->where(function ($query) use ($startTime, $endTime) {
                // الحجز يبدأ قبل نهاية الفترة المطلوبة وينتهي بعد بدايتها
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->whereIn('status', ['confirmed', 'pending']) // تحقق فقط من الحجوزات المؤكدة أو المعلقة
            ->exists();
    }

    /**
     * [HELPER] تتحقق من أن فترة الحجز المطلوبة تقع بالكامل ضمن فترة توافر واحدة محددة.
     */
    protected function isWithinAvailability(int $resourceId, Carbon $startTime, Carbon $endTime): bool
    {
        $date = $startTime->toDateString();
        $dayOfWeek = $startTime->dayOfWeekIso; // 1 (الاثنين) إلى 7 (الأحد)
        $timeStart = $startTime->format('H:i:s');
        $timeEnd = $endTime->format('H:i:s');

        // يجب أن يقع الحجز بالكامل ضمن فترة توافر واحدة محددة.
        $availability = Availability::where('resource_id', $resourceId)
            ->where('day_of_week', $dayOfWeek)
            // التحقق من أن تاريخ الحجز يقع ضمن نطاق date_from و date_to (إذا كانت محددة)
            ->where(function ($query) use ($date) {
                $query->whereNull('date_from') // قواعد التوافر الأسبوعية
                    ->orWhere(function ($q) use ($date) { // أو قواعد التوافر الخاصة بتاريخ
                        $q->whereDate('date_from', '<=', $date)
                            ->whereDate('date_to', '>=', $date);
                    });
            })
            // التحقق من أن وقت البدء والانتهاء يقعان ضمن وقت توافر واحد
            ->where('start_time', '<=', $timeStart)
            ->where('end_time', '>=', $timeEnd)
            ->first();

        return (bool) $availability;
    }

    /**
     * [HELPER] لدمج الفترات الحرة المتجاورة أو المتداخلة الناتجة عن تداخل فترات الـ Availability.
     */
    protected function mergeFreeIntervals(array $intervals): array
    {
        if (empty($intervals)) {
            return [];
        }

        // 1. فرز الفترات حسب وقت البدء
        usort($intervals, function ($a, $b) {
            return Carbon::parse($a['start']) <=> Carbon::parse($b['start']);
        });

        $merged = [];
        $currentInterval = $intervals[0];

        for ($i = 1; $i < count($intervals); $i++) {
            $nextInterval = $intervals[$i];
            $currentEnd = Carbon::parse($currentInterval['end']);
            $nextStart = Carbon::parse($nextInterval['start']);

            // إذا تداخلت الفترتان أو كانتا متجاورتين (بافتراض عدم وجود فرق كبير بالثواني/الدقائق)
            if ($currentEnd->greaterThanOrEqualTo($nextStart)) {
                // ادمج الفترتين: ابدأ من بداية الحالية، وانتهِ عند الأبعد
                $currentInterval['end'] = $currentEnd->max(Carbon::parse($nextInterval['end']))->toDateTimeString();
            } else {
                // لا يوجد تداخل، احفظ الفترة المدمجة الحالية وابدأ فترة جديدة
                $merged[] = $currentInterval;
                $currentInterval = $nextInterval;
            }
        }

        $merged[] = $currentInterval; // أضف الفترة الأخيرة

        return $merged;
    }
}
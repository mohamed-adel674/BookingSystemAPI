<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Resource;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * إحصائيات لوحة التحكم (GET /api/admin/statistics).
     */
    public function statistics()
    {
        $stats = [
            'total_users' => User::count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'total_resources' => Resource::count(),
            'active_resources' => Resource::where('is_active', true)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * عرض كل المستخدمين (GET /api/admin/users).
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);

        return response()->json($users);
    }

    /**
     * عرض كل الحجوزات (GET /api/admin/bookings).
     */
    public function allBookings(Request $request)
    {
        $query = Booking::with(['user:id,name,email', 'resource:id,name']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('start_time', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('start_time', '<=', $request->to_date);
        }

        $bookings = $query->latest()->paginate(20);

        return response()->json($bookings);
    }

    /**
     * تحديث دور المستخدم (PUT /api/admin/users/{userId}/role).
     */
    public function updateUserRole(Request $request, $userId)
    {
        $request->validate([
            'is_admin' => 'required|boolean',
        ]);

        $user = User::findOrFail($userId);
        $user->update(['is_admin' => $request->is_admin]);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user
        ]);
    }
}

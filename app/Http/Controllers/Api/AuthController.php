<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * تسجيل مستخدم جديد (Register).
     */
    public function register(Request $request)
    {
        // 1. Validation (التحقق من صحة البيانات)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Creation
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer', // افتراضياً هو عميل
        ]);

        // 3. Token Generation
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201); // 201 Created
    }

    /**
     * تسجيل الدخول والحصول على Token (Login).
     */
    public function login(Request $request)
    {
        // 1. Validation
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Authentication (التحقق من بيانات الاعتماد)
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // 3. Token Generation (إلغاء التوكنات القديمة وإنشاء توكن جديد)
        $user->tokens()->delete(); // للمحافظة على أمان الجلسة
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * تسجيل الخروج وإلغاء صلاحية Token (Logout).
     */
    public function logout(Request $request)
    {
        // استخدام currentAccessToken لـ Sanctum لإلغاء التوكن الحالي
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
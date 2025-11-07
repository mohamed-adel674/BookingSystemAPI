<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles  // <--- يمرر الأدوار المطلوبة مثل 'admin', 'manager'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. التحقق من أن المستخدم مسجل الدخول
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized: User not logged in.'], 401);
        }

        // 2. التحقق مما إذا كان دور المستخدم موجوداً ضمن الأدوار الممررة
        if (! in_array($request->user()->role, $roles)) {
            // خطأ 403 Forbidden إذا لم يكن الدور مسموحاً
            return response()->json([
                'message' => 'Forbidden: You do not have the required permissions.'
            ], 403); 
        }

        return $next($request);
    }
}
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

        // 2. التحقق مما إذا كان المستخدم مشرفاً (is_admin)
        if (! $request->user()->is_admin) {
            // خطأ 403 Forbidden إذا لم يكن المستخدم مشرفاً
            return response()->json([
                'message' => 'Forbidden: You do not have the required permissions.'
            ], 403); 
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = (new UserRepository())->find(auth()->id());
        $myPermissions = $user->getPermissionNames()->toArray();
        $roleNames = $user->getRoleNames()->toArray();
        $myRole = $roleNames[0] ?? null;

        $requestRoute = \request()->route()->getName();
        $roleLower = $myRole ? strtolower((string) $myRole) : '';

        // دور root (بدون اعتماد على حالة الأحرف) يمرّ على كل المسارات
        if ($roleLower === 'root') {
            return $next($request);
        }

        // صلاحية صريحة للمسار الحالي
        if (in_array($requestRoute, $myPermissions, true)) {
            return $next($request);
        }

        // السماح بدخول لوحة التحكم (root) لأي أدمن أو زائر حتى لو لم تُربط الصلاحيات
        if ($requestRoute === 'root' && in_array($roleLower, ['admin', 'visitor'], true)) {
            return $next($request);
        }

        return back()->with('error', 'Sorry, You have no permission');
    }
}

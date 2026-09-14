<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateVendor
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('vendor')->check()) {
            return redirect()->route('vendor.login');
        }

        $vendor = Auth::guard('vendor')->user();
        if ($vendor && $vendor->status !== 'active') {
            Auth::guard('vendor')->logout();
            $msg = $vendor->status === 'pending'
                ? 'Your account is pending administrative approval.'
                : 'Your vendor account has been deactivated.';

            return redirect()->route('vendor.login')->with('warning', $msg);
        }

        return $next($request);
    }
}

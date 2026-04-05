<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SingleDeviceLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $payload = auth('api')->payload();
        $user = auth('api')->user();
        if($user->remember_token != $payload->get('session_id')){
            auth('api')->logout();
            return response()->json([
                'message' => 'Logged in from another device. Please login again.',
            ], 401);
        }
        return $next($request);
    }
}

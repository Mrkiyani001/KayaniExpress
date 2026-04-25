<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class microservice_auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = $request->header('X-Microservice-Secret');
        if(!$secret){
            return response()->json([
                'success' => false,
                'message' => 'Secret key is required',
                'data' => null
            ], 401);
        }
        if($secret !== config('services.microservice.secret')){
            return response()->json([
                'success' => false,
                'message' => 'Invalid secret key',
                'data' => null
            ], 401);
        }
        return $next($request);
    }
}

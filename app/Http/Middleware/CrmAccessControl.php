<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CrmAccessControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if reads are disabled
        if (!config('crm.read_enabled') && $request->isMethod('GET')) {
            return response()->json([
                'message' => config('crm.disabled_message'),
                'status' => 'read_disabled'
            ], 503);
        }

        // Check if writes are disabled
        $writeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        if (!config('crm.write_enabled') && in_array($request->method(), $writeMethods)) {
            $message = config('crm.public_demo_mode') 
                ? config('crm.demo_write_disabled_message')
                : config('crm.disabled_message');

            return response()->json([
                'message' => $message,
                'status' => 'write_disabled',
                'demo_mode' => config('crm.public_demo_mode')
            ], 503);
        }

        return $next($request);
    }
}
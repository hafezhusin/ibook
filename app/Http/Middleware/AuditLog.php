<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLog
{
    // Route yang perlu dilog (operasi sensitif)
    protected array $sensitiveRoutes = [
        'POST:/login',
        'POST:/logout',
        'POST:/tempahan',
        'POST:/pengguna',
        'PUT:/pengguna',
        'DELETE:/bilik-mesyuarat',
        'POST:/tetapan',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $routeKey = strtoupper($request->method()) . ':' . $request->path();

        // Log hanya operasi sensitif
        $isSensitive = collect($this->sensitiveRoutes)->contains(function ($pattern) use ($routeKey) {
            return str_starts_with($routeKey, str_replace('*', '', $pattern));
        });

        if ($isSensitive || in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $userId = Auth::id() ?? 'tetamu';
            $status = $response->getStatusCode();

            Log::channel('stack')->info('AUDIT', [
                'user_id'  => $userId,
                'method'   => $request->method(),
                'path'     => $request->path(),
                'ip'       => $request->ip(),
                'status'   => $status,
                'agent'    => substr($request->userAgent() ?? '', 0, 100),
            ]);
        }

        return $response;
    }
}

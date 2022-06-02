<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // $response = $next($request);

        $log = [
            'URI' => $request->getUri(),//$request->path()
            'METHOD' => $request->getMethod(),
            'USER_AGENT' => $request->header('User-Agent'),
            'CONTENT_TYPE' => $request->header('content-type'),
            'HOST' => $request->header('host'),
            'HEADER' => json_encode($request->headers->all()),
            'REQUEST_BODY' => json_encode($request->all()),//$request->headers->all()
            // 'RESPONSE' => $response->getContent(),
        ];
        Log::info('********************* LOG REQUEST ***************************');
        Log::info($log);
        Log::info('*************************************************************');

        return $next($request);
    }
}

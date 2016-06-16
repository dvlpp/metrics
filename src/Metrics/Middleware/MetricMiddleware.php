<?php

namespace Dvlpp\Metrics\Middleware;

use Closure;

class MetricMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //
        dd($request);
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;

class Cross
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
        /*return $next($request)->header('Access-Control-Allow-Origin', '*')
         ->header('Access-Control-Allow-Credentials', 'true');*/
         header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Credentials: true");
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, Access-Control-Request-Headers, SERVER_NAME, Access-Control-Allow-Headers, cache-control, token,id, X-Requested-With, Content-Type, Accept, AUTHORIZATION, Connection, User-Agent, Cookie, X-XSRF-TOKEN');
            return $next($request);
    }
}

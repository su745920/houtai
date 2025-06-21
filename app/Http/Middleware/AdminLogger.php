<?php

namespace App\Http\Middleware;

use App\Models\AdminLogs;
use Closure;
use Illuminate\Support\Facades\Route;


class AdminLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            $uri = Route::getCurrentRoute()->uri();
            if (!preg_match('/admin\\/logs\\/.*/i', $uri)){
                $method = '';
                if ($request->isMethod('get')){
                    $method = 'get';
                }
                if ($request->isMethod('post')){
                    $method = 'post';
                }
                $params = $request->all();
                if (isset($params['password'])){
                    $params['password'] = str_repeat('*', 6);
                }
                
                $data = [
                    'admin_id'=>session()->get('admin_id') ?? 0,
                    'admin_name'=>session()->get('admin_username') ?? '',
                    'uri'=> $uri,
                    'method'=>$method,
                    'ajax'=>$request->ajax() ? 1 : 0,
                    'ip'=>$request->ip(),
                    'params'=>json_encode($params, JSON_UNESCAPED_UNICODE),
                ];
                AdminLogs::insert($data);
            }
        }catch(\Exception $e){
            report($e);
        }
        
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Users;
use App\Models\Token;
use Closure;
use Session;
use Illuminate\Support\Facades\Auth;

class CheckApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = Token::getToken();
        $user_id = Token::getUserIdByToken($token);
        if (empty($user_id)){
            $lang = $request->input('lang','en');
            if ($lang=="en"){
                return response()->json(['type'=>'999','message'=>'Please Login']);
            }
            if ($lang=="hk"){
                return response()->json(['type'=>'999','message'=>'請登入']);
            }

            return response()->json(['type'=>'999','message'=>'请登录']);
        }
        return $next($request);
    }
}

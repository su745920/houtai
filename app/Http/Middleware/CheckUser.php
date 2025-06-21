<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;
use App\Models\UserReal;
class CheckUser
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
        $user_id = Users::getUserId();
        $user_real = UserReal::where('user_id',$user_id)->first();
        //if(empty($user_real)){
        //    return response()->json(['type' => '998', 'message' => trans('login.qsmrz')]);
        //}
        //if ($user_real->review_status != 2){

        //    return response()->json(['type' => 'error', 'message' => trans('login.ndsmrzhwtg')]);
        //}
        return $next($request);
    }
}

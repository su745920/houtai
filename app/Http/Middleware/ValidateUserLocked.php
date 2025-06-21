<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Users;

class ValidateUserLocked
{

    public function handle($request, Closure $next)
    {
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if ($user) {
             if ($user->status == 0 && $user->lock_time > time()) {
                return response()->json(['type' => 'error', 'message' => '账号被冻结,'  . '暂时不能操作，请联系客服']);
            }
        } else {
            return response()->json(['type'=>'999','message'=>'请登录']);
        }
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\DAO\SafeDAO;
use Closure;
use App\Models\Users;
use App;
use Illuminate\Support\Facades\Cache;
class CheckPayPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $scene)
    {
        if (SafeDAO::checkNeedPassword($scene)) {
            $pay_password = $request->input('password', '');
            if (empty($pay_password)) {
                return response()->json([
                    'type' => 'error',
                    'message' => trans('login.qsrzfmm'),
                ]);
            }
            $user_id = Users::getUserId();
            $user = Users::find($user_id);
            $lang = Cache::get('lan_type_'.$user_id);
            // if($lang == ''){
            //   $lang = 'zh_cn'; 
            // }
            App::setLocale($lang);
            if (!$user) {
                return response()->json([
                    'type' => 'error',
                    'message' => trans('login.yhbcz'),
                ]);
            }
            $user_pay_password = $user->pay_password;
            if (empty($user_pay_password)) {
                 return response()->json([
                    'type' => 'error',
                    'message' => trans('login.nwszzfmm'),
                ]);
                
                
                //return response(trans('login.nwszzfmm'), 401);
            }
            if (Users::MakePassword($pay_password) != $user_pay_password) {
                return response()->json([
                    'type' => 'error',
                    'message' => trans('login.zfmmbcz'),
                ]);
            }
        }        
        return $next($request);
    }
}

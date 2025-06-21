<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\UserMatch;
use App;
class UserMatchController extends Controller
{
    public function lists(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = 50;
        $user_matches = UserMatch::where('user_id', $user_id)
            ->paginate($limit);
        return $this->success($user_matches);
    }

    public function add(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $currency_match_id = $request->input('id', 0);
        if ($currency_match_id <= 0) {
            return $this->error(trans('match.jydidcw'));
        }
        try {
            $user_match = UserMatch::where('user_id', $user_id)
                ->where('currency_match_id', $currency_match_id)
                ->first();
            if ($user_match) {
                return $this->error(trans('match.yjdytjgzx'));
            }
            UserMatch::unguard();
            $user_match = UserMatch::create([
                'user_id' => $user_id,
                'currency_match_id' => $currency_match_id,
            ]);
            if (!isset($user_match->id)) {
                throw new \Exception(trans('match.tjzxsb'));
            }
            return $this->success(trans('match.tjzxcg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        } finally {
            UserMatch::reguard();
        }
    }

    public function del(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $id = $request->input('id', 0);
        $user_match = UserMatch::where('user_id', $user_id)
            ->where('currency_match_id', $id)
            ->first();
        if (!$user_match) {
            return $this->error(trans('match.zdzxjydbcz'));
        }
        $result = $user_match->delete();
        return $result > 0 ? $this->success(trans('match.sczxyjdcg')) : $this->error(trans('match.sczxyjdsb'));
    }
}

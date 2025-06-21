<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{AccountLog, CzRewardGroup, Users, Setting, UsersWallet};
use Illuminate\Support\Facades\DB;

class ApiCzRewardGroupController extends Controller
{
    public function czRewardList(Request $request)
    {
        $uid = Users::getUserId();
        $data = CzRewardGroup::where("parent_uid", '=', $uid)->get();
        return $this->success($data);


    }

}
<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{AccountLog,
    Currency,
    CurrencyProjectOrder,
    MicroOrder,
    MicroSecond,
    Setting,
    Users,
    UsersWallet,
    RewardConf};
use Illuminate\Support\Facades\DB;

class AdminRewardController extends Controller
{

    public function saveConf(Request $request)
    {



        $id = $request->get("id");

        if (empty($id)) return $this->error("参数错误");

        $res = RewardConf::find($id);
        if (empty($res)) {
            return $this->error("数据未找到");
        }
        $ratio=$request->get("ratio");
        $res->ratio = $ratio;

        $min_amount=$request->get("min_amount");
        $res->min_amount=$min_amount;

        $max_amount=$request->get("max_amount");
        $res->max_amount=$max_amount;


        DB::beginTransaction();

        try {
            $res->save();

            DB::commit();
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function configEdit(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }

        $result = RewardConf::findOrNew($id);

        return view('admin.reward_conf.config_edit', ['result' => $result]);
    }

    public function reward_conf_page()
    {
        $authorityList=session()->get("authorityList");
        return view('admin.reward_conf.list',['authorityList' =>$authorityList]);

    }

    public function reward_conf_list(Request $request)
    {
        $limit = $request->get('limit', 10);
        $list = RewardConf::orderBy('id', 'asc')
            ->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);

    }

}
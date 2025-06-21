<?php

namespace App\Http\Controllers\Admin;

use App\Models\UserLevelModel;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function user_level()
    {
        $po=UserLevelModel::find(1);
        return view('admin.setting.user_level', ['po' => $po]);
    }
    public function postUserLevel(Request $request)
    {
        $po=UserLevelModel::find(1);



        $lv0=$request->input("lv0");
        if (!empty($lv0)&&$lv0>0){
            $po->lv0=$lv0;
        }
        //
        $lv1=$request->input("lv1");
        if (!empty($lv1)&&$lv1>0){
            $po->lv1=$lv1;
        }
        //
        $lv2=$request->input("lv2");
        if (!empty($lv2)&&$lv2>0){
            $po->lv2=$lv2;
        }
        $lv3=$request->input("lv3");
        if (!empty($lv3)&&$lv3>0){
            $po->lv3=$lv3;
        }
        $lv4=$request->input("lv4");
        if (!empty($lv4)&&$lv4>0){
            $po->lv4=$lv4;
        }
        $lv5=$request->input("lv5");
        if (!empty($lv5)&&$lv5>0){
            $po->lv5=$lv5;
        }
        $lv5=$request->input("lv5");
        if (!empty($lv5)&&$lv5>0){
            $po->lv5=$lv5;
        }

        $lv6=$request->input("lv6");
        if (!empty($lv6)&&$lv6>0){
            $po->lv6=$lv6;
        }

        $lv7=$request->input("lv7");
        if (!empty($lv7)&&$lv7>0){
            $po->lv7=$lv7;
        }
        $lv8=$request->input("lv8");
        if (!empty($lv8)&&$lv8>0){
            $po->lv8=$lv8;
        }

        $lv9=$request->input("lv9");
        if (!empty($lv9)&&$lv9>0){
            $po->lv9=$lv9;
        }

        $lv10=$request->input("lv10");
        if (!empty($lv10)&&$lv10>0){
            $po->lv10=$lv10;
        }

        $po->save();

        return $this->success('操作成功');

    }







    public function index()
    {
        $bouns = Setting::getValueByKey('user_bonus');
        $ecology = Setting::getValueByKey('ecology_bonus');
        $bonus = json_decode($bouns);
        $ecology = json_decode($ecology);
        return view('admin.settings.index', ['bouns' => $bonus, 'ecology' => $ecology]);
    }

    public function base()
    {
        $rate_exchange     = Setting::getValueByKey('rate_exchange');
        $company_eth_address       = Setting::getValueByKey('company_eth_address');
        $lock_daily_return       = Setting::getValueByKey('lock_daily_return');
        $version       = Setting::getValueByKey('version', '1.0');
        $transaction_fee       = Setting::getValueByKey('transaction_fee', []);
        $transaction_fee = @json_decode($transaction_fee, true);

        $results = array(
            'rate_exchange'     => $rate_exchange,
            'lock_daily_return'     => $lock_daily_return,
            'company_eth_address'       => $company_eth_address,
            'version'       => $version,
        );
        return view('admin.settings.base', ['results' => $results, "transaction_fee" => $transaction_fee]);
    }

    public function setBase(Request $request)
    {
        $data = $request->all();

        $transaction_fee = array(
            "service_one_min" => $data["service_one_min"],
            "service_one_max" => $data["service_one_max"],
            "service_one_proportion" => $data["service_one_proportion"],
            "service_two_min" => $data["service_two_min"],
            "service_two_max" => $data["service_two_max"],
            "service_two_proportion" => $data["service_two_proportion"],
            "service_three_min" => $data["service_three_min"],
            "service_three_max" => $data["service_three_max"],
            "service_three_proportion" => $data["service_three_proportion"],
        );

        foreach ($data as $key => $value) {
            if (isset($transaction_fee[$key])) {
                continue;
            }
            switch ($key) {
                case 'rate_exchange':
                    break;
                case 'company_eth_address':
                    break;
                case 'lock_daily_return':
                    break;
                case 'version':
                    break;
            }
            Setting::updateValueByKey($key, $value);
        }

        $transaction_fee = json_encode($transaction_fee);
        Setting::updateValueByKey("transaction_fee", $transaction_fee);

        return $this->success('操作成功');
    }

    public function Insert()
    {
        $bouns = request()->input('bonus', '');
        $total_arr = request()->input('total_arr', '');
        $ecology = request()->input('ecology', '');
        $ecology_arr = request()->input('ecology_arr', '');

        $total_arr = json_encode($total_arr);
        $ecology_arr = json_encode($ecology_arr);

        if (empty($bouns) || empty($total_arr)) {
            return $this->error('日均收益参数错误');
        }
        if (empty($ecology) || empty($ecology_arr)) {
            return $this->error('推广奖励参数错误');
        }
        try {
            Setting::updateValueByKey($bouns, $total_arr);
            Setting::updateValueByKey($ecology, $ecology_arr);
            return $this->success('设置成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

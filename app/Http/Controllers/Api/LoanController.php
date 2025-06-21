<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{LoanSetting,LoanOrder, RepaymentRecord, Users};
use App\Models\Setting;
use App;

class LoanController extends Controller
{
    // 获取贷款费率
    public function getLoanFee()
    {
        $results = Setting::getValueByKey('loan_fee', 0);
        return $this->success($results);
    }
    // 获取贷款设置
    public function getSettingList()
    {
        $results = LoanSetting::where('status', 1)->orderBy('sorts')->get(['id','loan_days','loan_rate','loan_institution'])->toArray();
        return $this->success($results);
    }
    // 贷款列表
    public function getList(Request $request) {
        $user_id = Users::getUserId();
        $limit = request()->input("limit", 10);
        $loan_order = LoanOrder::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->success($loan_order);
    }
    // 贷款申请提交
    public function submit(Request $request) {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id= Users::getUserId();
        $loan_money = $request->input('loan_money', 0); // 贷款金额
        $house_prove = $request->input('house_prove', ''); // 房屋信息
        $income_prove = $request->input('income_prove', ''); // 收入证明
        $bank_records = $request->input('bank_records', ''); // 银行明细
        $photo = $request->input('photo', ''); // 证件照
        $loan_setting_id = $request->input('loan_setting_id',0); // 贷款设置信息
        $loan_fee = Setting::getValueByKey('loan_fee', 0); // 手续费费率
        
        // 获取贷款设置信息
        $loanSetting = LoanSetting::find($loan_setting_id);
        
        $loan_days = $loanSetting->loan_days; // 贷款天数
        $loan_rate = $loanSetting->loan_rate; // 日利率
        $loan_institution = $loanSetting->loan_institution; // 放款机构
        $interest = bc_mul($loan_money,$loan_rate,6); // 贷款利息 金额10000乘以1%的日利率乘以3天
        $interest = bc_mul($interest,$loan_days,6);
        $interest = bc_div($interest,100,6);
        $commission = $loan_money * $loan_fee; // 手续费
        $commission = bc_div($commission,100,6);
        
        if (empty($user_id) || empty($loan_money) || empty($loan_days) || empty($house_prove) || empty($income_prove) || empty($bank_records) || empty($photo)) {
            return $this->error(trans('transaction.cscw'));
        }
        
        try {
            DB::beginTransaction();
             $loan_order = new LoanOrder();
             $loan_order->user_id = $user_id;
             $loan_order->order_no = date('YmdHis').rand(100, 999);
             $loan_order->loan_setting_id = $loan_setting_id;
             $loan_order->loan_money = $loan_money;
             $loan_order->loan_days = $loan_days;
             $loan_order->loan_rate = $loan_rate;
             $loan_order->interest = $interest;
             $loan_order->loan_fee = $loan_fee;
             $loan_order->commission = $commission;
             $loan_order->loan_institution = $loan_institution;
             $loan_order->house_prove = $house_prove;
             $loan_order->income_prove = $income_prove;
             $loan_order->bank_records = $bank_records;
             $loan_order->photo = $photo;
             $loan_order->status = 0;
             
             $result = $loan_order->save();
             if (!$result) {
                throw new \Exception(trans('lever.tjsb'));
            }
            DB::commit();
            // 机器人推送消息
            robotSendMessage($user_id,'申请贷款'.$loan_money);
            return $this->success(trans('lever.tjcg'));
        } catch (\Exception $ex ) {
             DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    // 贷款详情
    public function getDetail(Request $request) {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = request()->input('id',0);
        $user_id= Users::getUserId();
        if (empty($user_id) || empty($id)) {
            return $this->error(trans('transaction.cscw'));
        }
        
        $loan_order = LoanOrder::find($id);
        if(empty($loan_order)) {
            return $this->error(trans('common.dkbcz'));
        }
        return $this->success($loan_order);
    }
    
    // 还款
    public function repayment(Request $request) {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = request()->input('id',0); // 贷款id
        $currency = request()->input('currency',23); // 币种id
        $currency_name = request()->input('currency_name','USDT'); // 币种名称
        $repayment_amount = request()->input('repayment_amount',0); // 还款金额USDT
        $repayment_voucher = request()->input('repayment_voucher',''); // 还款凭证
        $repayment_number = request()->input('repayment_number',0); // 还款数量
        
        $user_id= Users::getUserId();
        if (empty($user_id) || empty($id) || empty($repayment_amount) || empty($repayment_voucher) || empty($repayment_number)) {
            return $this->error(trans('transaction.cscw'));
        }
        // 获取贷款信息
        $loan_order = LoanOrder::find($id);
        // 应还款金额 = 贷款金额 + 贷款利息 + 手续费 - 已还金额
        $payable_amount = $loan_order->loan_money + $loan_order->interest + $loan_order->commission -  $loan_order->already_money;
        // 剩余待还金额
        $residue_amount = bc_sub($payable_amount,$repayment_amount,6); 
        
        if($currency == 231) {
            $currency = 23;
        }
        
        try {
            DB::beginTransaction();
            $repayment_records = new RepaymentRecord();
            $repayment_records->order_no = date('YmdHis').rand(100, 999);
            $repayment_records->user_id = $user_id;
            $repayment_records->loan_order_id = $id;
            $repayment_records->currency_id = $currency;
            $repayment_records->payable_amount = $payable_amount;
            $repayment_records->residue_amount = $residue_amount;
            $repayment_records->repayment_amount = $repayment_amount;
            $repayment_records->repayment_number = bc_mul(1,$repayment_number,6);
            $repayment_records->repayment_voucher = $repayment_voucher;
            $repayment_records->status = 0;
            
            $result = $repayment_records->save();
             if (!$result) {
                throw new \Exception(trans('lever.tjsb'));
            }
            
            $loan_order->status = 4; // 状态为还款中
            $loan_order->save();
            // 机器人推送消息
            robotSendMessage($user_id,'申请还款'.bc_mul(1,$repayment_amount,2).'（'.$currency_name.'）');
            DB::commit();
            return $this->success(trans('lever.tjcg'));
        } catch (\Exception $ex ) {
             DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
}

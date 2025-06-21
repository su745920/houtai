<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{AccountLog, Currency, Transfer, Users, UsersWallet};
use App;

class TransferController extends Controller
{
    public function getAllowTransferFee()
    {
        $currencies = Currency::where('allow_transfer', 1)->all();
        return $this->success($currencies);
    }

    public function submit(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $currency_id = $request->input('currency_id', 0);
        $account_number = $request->input('account_number', '');
        $number = $request->input('number', 0);
        $memo = $request->input('memo', '');
        try {
            DB::beginTransaction();
            $to_user = Users::getByString($account_number);
            if (empty($to_user)) {
                throw new \Exception(trans('user.cyhbcz'));
            }
            if ($to_user->id == $user_id) {
                throw new \Exception(trans('transfer.byxxzjzz'));
            }
            $currency = Currency::findOrFail($currency_id);
            if (!$currency->allow_transfer) {
                throw new \Exception(trans('transfer.dqbzbyxznzz'));
            }
            if (bc_comp_zero($number) <= 0) {
                throw new \Exception(trans('transfer.slbxdy'));
            }
            
            $from_wallet = UsersWallet::where('user_id', $user_id)
                ->where('currency', $currency_id)
                ->lockForUpdate()
                ->first();
            $to_wallet = UsersWallet::where('user_id', $to_user->id)
                ->where('currency', $currency_id)
                ->lockForUpdate()
                ->first();
            if (!$from_wallet) {
                throw new \Exception(trans('transfer.ndqbbcz',['current_name' => $currency->name]));
            }
            if (!$to_wallet) {
                throw new \Exception(trans('transfer.jsfdqbbcz',['current_name' => $currency->name]));
            }
            //1.法币,2.币币交易,3.杠杆交易
            $from_result = change_wallet_balance($from_wallet, 2, -$number, AccountLog::TRANSFER_TO, "站内转账转出","In station transfer out");
            if ($from_result !== true) {
                throw new \Exception($from_result);
            }
            $to_result = change_wallet_balance($to_wallet, 2, $number, AccountLog::TRANSFER_TO, "站内转账收款","In station transfer collection");
            if ($to_result !== true) {
                throw new \Exception($to_result);
            }
            $transfer_data = [
                'currency_id' => $currency_id,
                'from_user_id' => $user_id,
                'to_user_id' => $to_user->id,
                'from_number' => $number,
                'to_number' => $number,
                'fact_fee' => 0,
                'memo' => $memo,
            ];
            Transfer::unguarded(function () use ($transfer_data) {
                return Transfer::create($transfer_data);
            });
            DB::commit();
            return $this->success(trans('transfer.znjycg'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(trans('transfer.znjysb') . $e->getMessage());
        }
    }

    public function logs(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->input('limit', 10);
        $logs = Transfer::where('from_user_id', $user_id)
            ->where(function ($query) use ($request) {
                $currency_id = $request->input('currency_id', 0);
                $currency_id > 0 && $query->where('currency_id', $currency_id);
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->submit($logs);
    }
}

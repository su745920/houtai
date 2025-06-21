<?php

namespace App\Listeners;

use App\Events\WithdrawSubmitEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\DAO\GoChainDAO;
use App\Models\Currency;

class WithdrawSubmitListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  WithdrawSubmitEvent  $event
     * @return void
     */
    public function handle(WithdrawSubmitEvent $event)
    {
        $withdraw = $event->withdraw;
        $withdraw->refresh();
        if ($withdraw->status != 1 || bc_comp($withdraw->real_number, '0') <= 0) {
            throw new \Exception('提币信息状态异常');
        }
        $currency = $withdraw->currencyCoin;
        if ($currency->multi_protocol == 1) {
            $currency = Currency::where('parent_id', $withdraw->currency)
                ->where('multi_protocol', 0)
                ->where('type', $withdraw->type)
                ->firstOrFail();
        }

        $result = GoChainDAO::submitUserWithdraw($withdraw, $currency);
        if (!isset($result['code']) || $result['code'] != 0) {
            throw new \Exception('同步信息失败,' . $result['errorinfo']);
        }
    }
}

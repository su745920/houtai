<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\LeverTransaction;
use App\Models\CurrencyQuotation;

use Log;

class LeverUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        extract($this->params);
         // 插针数据替换
        $data = CurrencyQuotation::getCurrencyQuotationIdDetail($currency_id,$legal_id);
        if($data) {
            $now_price = $data->close;
        }
        //价格大于0才做更新处理
        if (bc_comp($now_price, '0') > 0) {
            // Log::info("价格变动");
            LeverTransaction::newPrice($legal_id, $currency_id, $now_price, $now);
        }
    }
}

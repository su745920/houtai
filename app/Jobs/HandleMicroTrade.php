<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use App\Logic\MicroTradeLogic;
use App\Models\MicroOrder;
use App\Models\CurrencyQuotation;

class HandleMicroTrade implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $klineData = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($kline_data)
    {
        $this->klineData = $kline_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = base_path() . '/storage/logs/test/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        //error_log(date('Y-m-d H:i:s') . 'HandleMicroTrade.handle'. PHP_EOL, 3, $path . $filename);
        

        $match_id = $this->klineData['match_id'];
        $now_price = $this->klineData['close'];
        // 插针数据替换
        $data = CurrencyQuotation::getCurrencyQuotationIdDetail($this->klineData['currency_id'],$this->klineData['legal_id']);
        if($data) {
            $now_price = $data->close;
        }
        $res = MicroTradeLogic::newPrice($match_id, $now_price);
        MicroTradeLogic::close($match_id);

    }
}

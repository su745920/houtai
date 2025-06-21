<?php

namespace App\Jobs;

use App\Logic\SocketLogic;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\UserChat;

class SendMarket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $marketData;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($market_data)
    {
        $this->marketData = $market_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // echo '发送数据数据SocketLogic' . PHP_EOL;
        // if($this->marketData['type'] == "lever_trade"){
        //     echo '发送数据'.json_encode($this->marketData) . PHP_EOL;
        // }
        SocketLogic::sendMsg($this->marketData);
        //echo '发送数据数据sendText' . PHP_EOL;
        UserChat::sendText($this->marketData);
        
        //UserChat::sendChat($this->marketData);
    }
}

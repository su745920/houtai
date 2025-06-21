<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\MicroOrder;
use App\Models\UserChat;

class SendClosedMicroOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MicroOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $send_data = [
            'type' => 'closed_microorder',
            'to' => $this->order->user_id,
            'data' => array(
                "id" => $this->order->id
            ),
        ];
        UserChat::sendText($send_data);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;

class WebSocketAlltickClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocketalltick:client {worker_command} {--mode=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'websocket alltick client';

    protected $worker;

    protected $events = [
        'onWorkerStart',
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onWorkerStop',
        'onWorkerReload'
    ];

    protected $callback_class;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initWorker();
        $this->bindEvent();
        Worker::runAll();
    }

    public function parseArguments()
    {
        global $argv;
        $argv[1] = $command = $this->argument('worker_command');
        $mode = $this->option('mode');
        isset($mode) && $argv[2] = '-' . $mode;
    }

    protected function initWorker()
    {
        $class_name = config('websocket.alltick_client.callback_class');
        $process_num = config('websocket.alltick_client.process_num');
        $this->callback_class = new $class_name();
        $this->worker = new Worker();
        $this->worker->count = $process_num;
        $this->worker->name = 'Alltick Websocket';
        $this->parseArguments();
        $path = storage_path() .  '/gateway/';
        file_exists($path) || @mkdir($path);
        Worker::$pidFile = $path . 'alltick_wss.pid';
    }

    protected function bindEvent()
    {
        foreach ($this->events as $key => $event) {
            method_exists($this->callback_class, $event) && $this->worker->$event = [$this->callback_class, $event];
        }
    }
}

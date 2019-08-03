<?php

namespace App\Jobs\Timer;

use App\Jobs\Task\AuctionSpiderTask;
use App\Jobs\Task\AuctionSpiserTask;
use App\Library\Spider\AuctionSpider;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Illuminate\Support\Facades\Log;

class AuctionSpiderTimer extends CronJob
{
    protected $i = 0;

    public function run()
    {
        Task::deliver(new AuctionSpiderTask(AuctionSpider::REGISTRAR_22));
        Task::deliver(new AuctionSpiderTask(AuctionSpider::REGISTRAR_ENAME));
        Task::deliver(new AuctionSpiderTask(AuctionSpider::REGISTRAR_ALIYUN));
    }

    //定时器间隔(ms)
    public function interval()
    {
        return config('timer.auction_spider_interval');
    }

    //是否立即触发
    public function isImmediate()
    {
        return true;
    }
}
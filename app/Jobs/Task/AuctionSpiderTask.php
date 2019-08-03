<?php

namespace App\Jobs\Task;

use App\Library\Spider\AuctionSpider;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

class AuctionSpiderTask extends Task
{
    private $site;

    public function __construct($site)
    {
        $this->site = $site;
    }

    public function handle()
    {
        $spider = new AuctionSpider($this->site);
        $spider->handle();
    }

    public function finish()
    {
    }
}
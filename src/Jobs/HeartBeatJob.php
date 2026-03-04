<?php

namespace Soukar\QAlert\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class HeartBeatJob
    implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public $key ,
        public $time ,
        public $stayInCache
    )
    {}

    public function handle(): void {
        Cache::put(
            $this->key,
            $this->time,
            $this->stayInCache
        );
    }
}

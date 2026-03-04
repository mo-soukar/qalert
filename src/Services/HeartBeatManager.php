<?php

namespace Soukar\QAlert\Services;

use Illuminate\Support\Facades\Cache;
use Soukar\QAlert\Jobs\HeartBeatJob;

class HeartBeatManager
{

    private $watchers;

    public function __construct() {
        $this->watchers = config('qalert.heartbeat.watchers',[]);
    }

    public function getWatchers()
    {
        return $this->watchers;
    }



    public function put(
        string $queue,
        string $connection
    ) {

        $key = "qalert:heartbeat:{$connection}:{$queue}";
        $time = now()->timestamp;

        HeartBeatJob::dispatch($key , $time , now()->addMinutes(
            config('qalert.heartbeat.threshold', 5) +  2
        ))->onQueue($queue)->onConnection($connection);

    }

    public function check(
        string $queue,
        string $connection
    ): bool {

        $key = "qalert:heartbeat:{$connection}:{$queue}";
        $timestamp = Cache::get($key);

        if (!$timestamp) {
            return false;
        }
        $lastBeat = \Carbon\Carbon::createFromTimestamp($timestamp);

        $threshold = config('qalert.heartbeat.threshold');

        return abs(now()->diffInMinutes($lastBeat)) <= $threshold;

    }
}
<?php

namespace Soukar\QAlert\Services;

use Illuminate\Support\Facades\Log;
use Soukar\QAlert\Entities\EventParser;

class QAlertManager {
    public function __construct(
        public ChannelManager $channelManager,
        public HeartBeatManager $heartBeatManager,
    ) {}


    public function handle($event)
    {
        $this->channelManager->sendMessageToAllChannels("⚠️ Failure Detected in " . config('app.name'), ['event' => new EventParser($event)]);
    }



    public function handleHeartBeat()
    {
        foreach ($this->heartBeatManager->getWatchers() as $watcher) {
            foreach (explode( ",",$watcher['queues']) as $queue)
            {

                $this->heartBeatManager->put(
                    $queue,
                    $watcher['connection']
                );
            }
        }
    }

    public function checkHeartBeat()
    {

        foreach ($this->heartBeatManager->getWatchers() as $watcher) {
            foreach (explode( ",",$watcher['queues']) as $queue) {
                if(!$this->heartBeatManager->check($queue, $watcher['connection']))
                {
                    $this->channelManager->sendMessageToAllChannels(
                        "⚠️ Failure Detected in " . config('app.name')."\n".
                        "*CRITICAL: Queue Stalled*\n" .
                                                                     "Connection: `{$watcher['connection']}`\n" .
                                                                     "Queue: `{$queue}`\n" .
                                                                     "Status: No test jobs processed recently!");
                }
            }

        }
    }



    public function __call($method, $arguments)
    {
         if(method_exists($this->channelManager , $method)){
            return $this->channelManager->$method(...$arguments);
         }elseif(method_exists($this->heartBeatManager , $method)){
             return $this->heartBeatManager->$method(...$arguments);
         }else{
             throw new \BadMethodCallException();
         }
    }
}
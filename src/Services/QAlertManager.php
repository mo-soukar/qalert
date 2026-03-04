<?php

namespace Soukar\QAlert\Services;

use Illuminate\Support\Facades\Log;
use QAlert;
use Soukar\QAlert\Entities\EventParser;

class QAlertManager {
    public function __construct(
        public ChannelManager $channelManager,
    ) {}


    public function handle($event)
    {
        foreach ($this->channelManager->getChannels() as $channel) {
            try {
                $this->channelManager->getChannelObject($channel)->send("⚠️ Failure Detected in " . config('app.name'), ['event' => new EventParser($event)]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("QAlert Channel Failed: " . $e->getMessage());
            }
        }
    }

    public function __call($method, $arguments)
    {
        return $this->channelManager->$method(...$arguments);
    }
}
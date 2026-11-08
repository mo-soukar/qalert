<?php

namespace Soukar\QAlert\Services;

use Soukar\QAlert\Entities\EventParser;

class QAlertManager
{
    protected static bool $isHandling = false;

    public function __construct(
        public ChannelManager $channelManager,
        public HeartBeatManager $heartBeatManager,
    ) {}

    public function handle($event)
    {
        if (self::$isHandling) {
            return;
        }

        self::$isHandling = true;
        try {
            $this->channelManager->sendMessageToAllChannels(
                "⚠️ Failure Detected in " . config('app.name'),
                ['event' => new EventParser($event)]
            );
        } finally {
            self::$isHandling = false;
        }
    }

    /**
     * Send or update a progress message in Telegram.
     */
    public function progress(?int $messageId, string $text, array $buttons = [], string $parseMode = 'Markdown'): ?int
    {
        return $this->channelManager->progress($messageId, $text, $buttons, $parseMode);
    }

    public function sendMessage(string $text, array $buttons = [], string $parseMode = 'Markdown')
    {
        return $this->channelManager->sendMessage($text, $buttons, $parseMode);
    }

    public function editMessage(int $messageId, string $newText, array $buttons = [], string $parseMode = 'Markdown')
    {
        return $this->channelManager->editMessage($messageId, $newText, $buttons, $parseMode);
    }

    public function handleHeartBeat()
    {
        foreach ($this->heartBeatManager->getWatchers() as $watcher) {
            foreach (explode(",", $watcher['queues']) as $queue) {
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
            foreach (explode(",", $watcher['queues']) as $queue) {
                if (!$this->heartBeatManager->check($queue, $watcher['connection'])) {
                    $this->channelManager->sendMessageToAllChannels(
                        "⚠️ Failure Detected in " . config('app.name') . "\n" .
                        "*CRITICAL: Queue Stalled*\n" .
                        "Connection: `{$watcher['connection']}`\n" .
                        "Queue: `{$queue}`\n" .
                        "Status: No test jobs processed recently!"
                    );
                }
            }
        }
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->channelManager, $method)) {
            return $this->channelManager->$method(...$arguments);
        } elseif (method_exists($this->heartBeatManager, $method)) {
            return $this->heartBeatManager->$method(...$arguments);
        } else {
            throw new \BadMethodCallException("Method [{$method}] does not exist on QAlertManager.");
        }
    }
}

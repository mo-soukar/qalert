<?php

namespace Soukar\QAlert\Services;


use Soukar\QAlert\Channels\TelegramChannel;

class ChannelManager {

    private array $channels = [];

    public function __construct() {
        $this->channels[] = config('qalert.default_channel');
    }

    public function withTelegram()
    {
        if(!in_array('telegram', $this->channels)) {
            $this->channels[] = 'telegram';
        }
        return $this;
    }


    public function getChannels(): array
    {
        return $this->channels;
    }

    public function getChannelObject(string $channel)
    {
        return match ($channel) {
            'telegram' => app(TelegramChannel::class),
        };
    }

    public function sendMessageToAllChannels(string $message , array $options = [])
    {
        foreach ($this->getChannels() as $channel) {
            try {
                $this->getChannelObject($channel)->send($message, $options);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("QAlert Channel Failed: " . $e->getMessage());
            }
        }
    }


}
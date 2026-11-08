<?php

namespace Soukar\QAlert\Services;

use Soukar\QAlert\Channels\TelegramChannel;

class ChannelManager
{
    private array $channels = [];

    public function __construct()
    {
        $this->channels[] = config('qalert.default_channel', 'telegram');
    }

    public function withTelegram()
    {
        if (!in_array('telegram', $this->channels)) {
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

    public function sendMessageToAllChannels(string $message, array $options = [])
    {
        foreach ($this->getChannels() as $channel) {
            try {
                $this->getChannelObject($channel)->send($message, $options);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::channel('single_qalert')->error("QAlert Channel Failed: " . $e->getMessage());
            }
        }
    }

    public function sendMessage(string $message, array $buttons = [], string $parseMode = 'Markdown')
    {
        return app(TelegramService::class)->sendMessage($message, $buttons, $parseMode);
    }

    public function editMessage(int $messageId, string $newText, array $buttons = [], string $parseMode = 'Markdown')
    {
        return app(TelegramService::class)->editMessage($messageId, $newText, $buttons, $parseMode);
    }

    /**
     * Send initial progress message or edit existing one. Returns message_id or null.
     */
    public function progress(?int $messageId, string $text, array $buttons = [], string $parseMode = 'Markdown'): ?int
    {
        return app(TelegramService::class)->sendOrEditMessage($messageId, $text, $buttons, $parseMode);
    }
}

<?php

namespace Soukar\QAlert\Channels;

use Soukar\QAlert\Services\TelegramService;

class TelegramChannel implements NotificationChannel
{
    public function __construct(
        private readonly TelegramService $telegramService
    ) {}

    public function send(string $message, array $payload = [])
    {
        if (array_key_exists('event', $payload)) {
            $eventParser = $payload['event'];
            $message = sprintf($message . "\n" . $eventParser->getMessage());
        }
        $buttons = $payload['buttons'] ?? [];
        $parseMode = $payload['parse_mode'] ?? 'Markdown';
        return $this->telegramService->sendMessage($message, $buttons, $parseMode);
    }
}

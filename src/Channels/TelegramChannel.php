<?php


namespace Soukar\QAlert\Channels;
use Illuminate\Support\Facades\Log;
use Soukar\QAlert\Services\TelegramService;
class TelegramChannel implements NotificationChannel {

    public function __construct(
        private readonly  TelegramService $telegramService
    ) {}

    public function send(string $message, array $payload=[])
    {
        $eventParser = $payload['event'];
        $message = sprintf($message."\n".$eventParser->getMessage());
        $this->telegramService->sendMessage($message);
    }
}
<?php

namespace Soukar\QAlert\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $chatId;

    public function __construct()
    {
        $this->token = config('qalert.channels.telegram.bot_token');
        $this->chatId = config('qalert.channels.telegram.chat_id');
    }

    public function sendMessage(string $text, array $buttons = [])
    {
        try {
            $payload = [
                'chat_id'    => $this->chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ];

            if (!empty($buttons)) {
                $payload['reply_markup'] = json_encode([
                                                           'inline_keyboard' => $buttons
                                                       ]);
            }

            $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", $payload);

            if ($response->failed()) {
                Log::error("QAlert Telegram Error: " . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("QAlert Telegram Exception: " . $e->getMessage());
            return false;
        }
    }


    public function editMessage(int $messageId, string $newText)
    {
        return Http::post("https://api.telegram.org/bot{$this->token}/editMessageText", [
            'chat_id'    => $this->chatId,
            'message_id' => $messageId,
            'text'       => $newText,
        ]);
    }
}
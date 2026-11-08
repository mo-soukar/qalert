<?php

namespace Soukar\QAlert\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $token;
    protected ?string $chatId;
    protected static float $lastRequestTimestamp = 0;

    public function __construct()
    {
        $this->token = config('qalert.channels.telegram.bot_token');
        $this->chatId = config('qalert.channels.telegram.chat_id');
    }

    /**
     * Send a new message to Telegram.
     */
    public function sendMessage(string $text, array $buttons = [], string $parseMode = 'Markdown')
    {
        $payload = [
            'chat_id'    => $this->chatId,
            'text'       => $this->sanitizeText($text),
            'parse_mode' => $parseMode,
        ];

        if (!empty($buttons)) {
            $payload['reply_markup'] = json_encode([
                'inline_keyboard' => $buttons
            ]);
        }

        return $this->sendRequest('sendMessage', $payload);
    }

    /**
     * Edit an existing message in Telegram.
     */
    public function editMessage(int $messageId, string $newText, array $buttons = [], string $parseMode = 'Markdown')
    {
        $payload = [
            'chat_id'    => $this->chatId,
            'message_id' => $messageId,
            'text'       => $this->sanitizeText($newText),
            'parse_mode' => $parseMode,
        ];

        if (!empty($buttons)) {
            $payload['reply_markup'] = json_encode([
                'inline_keyboard' => $buttons
            ]);
        }

        return $this->sendRequest('editMessageText', $payload);
    }

    /**
     * Send a new message if messageId is null, otherwise edit the existing message.
     * Returns the message_id (int) on success, or null on failure.
     */
    public function sendOrEditMessage(?int $messageId, string $text, array $buttons = [], string $parseMode = 'Markdown'): ?int
    {
        if (empty($messageId)) {
            $response = $this->sendMessage($text, $buttons, $parseMode);
            return $response['result']['message_id'] ?? null;
        }

        $response = $this->editMessage($messageId, $text, $buttons, $parseMode);
        if ($response !== false) {
            return $messageId;
        }

        // If edit failed because message was deleted or invalid, fallback to sending a new message
        $newResponse = $this->sendMessage($text, $buttons, $parseMode);
        return $newResponse['result']['message_id'] ?? null;
    }

    /**
     * Internal request runner with rate-limiting throttling and 429 retry backoff.
     */
    protected function sendRequest(string $endpoint, array $payload, int $maxRetries = 3)
    {
        if (empty($this->token) || empty($this->chatId)) {
            $this->safeLog('warning', "QAlert Telegram: Missing bot_token or chat_id.");
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->token}/{$endpoint}";

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $this->throttle();

            try {
                $response = Http::timeout(10)->post($url, $payload);

                // 1. Handle HTTP 429 (Too Many Requests / Rate Limiting)
                if ($response->status() === 429 || ($response->json('error_code') ?? 0) === 429) {
                    $retryAfter = (int) ($response->json('parameters.retry_after') ?? 2);
                    $this->safeLog('warning', "QAlert Telegram 429 Too Many Requests (attempt {$attempt}/{$maxRetries}). Sleeping {$retryAfter}s before retry.");

                    if ($retryAfter > 30) {
                        $this->safeLog('error', "QAlert Telegram: retry_after too high ({$retryAfter}s). Aborting retry.");
                        return false;
                    }

                    sleep(max($retryAfter, 1));
                    continue;
                }

                // 2. Handle 400 Bad Request if Markdown entity parsing failed
                if ($response->status() === 400 && isset($payload['parse_mode'])) {
                    $desc = strtolower((string) $response->json('description'));
                    if (str_contains($desc, "can't parse entities") || str_contains($desc, 'character') || str_contains($desc, 'entity')) {
                        $this->safeLog('warning', "QAlert Telegram: Markdown parse error. Retrying without parse_mode.");
                        unset($payload['parse_mode']);
                        continue;
                    }

                    // "message is not modified" is a normal non-error for editMessageText
                    if (str_contains($desc, 'message is not modified')) {
                        return $response->json();
                    }
                }

                if ($response->failed()) {
                    $this->safeLog('error', "QAlert Telegram Error [{$endpoint}]: " . $response->body());
                    return false;
                }

                return $response->json();
            } catch (\Throwable $e) {
                $this->safeLog('error', "QAlert Telegram Exception [{$endpoint}]: " . $e->getMessage());
                if ($attempt === $maxRetries) {
                    return false;
                }
                sleep(1);
            }
        }

        return false;
    }

    /**
     * Proactive throttling: ensure at least 1.0s gap between requests to the same chat.
     */
    protected function throttle(): void
    {
        $now = microtime(true);
        $elapsed = $now - self::$lastRequestTimestamp;
        $minInterval = 1.0; // 1 second interval between requests to avoid Telegram 429

        if ($elapsed < $minInterval && self::$lastRequestTimestamp > 0) {
            $sleepMicroseconds = (int) (($minInterval - $elapsed) * 1000000);
            usleep(min($sleepMicroseconds, 1000000));
        }

        self::$lastRequestTimestamp = microtime(true);
    }

    /**
     * Telegram max message length is 4096 characters.
     */
    protected function sanitizeText(string $text): string
    {
        if (mb_strlen($text) > 4000) {
            return mb_substr($text, 0, 3950) . "\n\n... [truncated by QAlert]";
        }
        return $text;
    }

    /**
     * Safely log without risking exceptions or recursive loops.
     */
    protected function safeLog(string $level, string $message): void
    {
        try {
            Log::channel('single_qalert')->$level($message);
        } catch (\Throwable) {
            // Silently swallow to prevent infinite loop or unhandled exceptions
        }
    }
}

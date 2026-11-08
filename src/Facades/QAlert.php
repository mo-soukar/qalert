<?php

namespace Soukar\QAlert\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void handle($event)
 * @method static void sendMessageToAllChannels(string $message, array $options = [])
 * @method static mixed sendMessage(string $text, array $buttons = [], string $parseMode = 'Markdown')
 * @method static mixed editMessage(int $messageId, string $newText, array $buttons = [], string $parseMode = 'Markdown')
 * @method static int|null progress(?int $messageId, string $text, array $buttons = [], string $parseMode = 'Markdown')
 * @method static void handleHeartBeat()
 * @method static void checkHeartBeat()
 *
 * @see \Soukar\QAlert\Services\QAlertManager
 */
class QAlert extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'qalert-manager';
    }
}

<?php

namespace Soukar\QAlert\Services;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Soukar\QAlert\Facades\QAlert;

class LogHandler extends AbstractProcessingHandler
{
    protected static bool $isHandling = false;

    public function __construct($level = \Monolog\Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        if (self::$isHandling) {
            return; // Prevent recursive infinite loop
        }

        self::$isHandling = true;
        try {
            $message = $record->formatted;
            QAlert::sendMessageToAllChannels($message);
        } catch (\Throwable $e) {
            // Never let logging failure propagate and trigger infinite recursion
        } finally {
            self::$isHandling = false;
        }
    }
}

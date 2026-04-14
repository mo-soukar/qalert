<?php

namespace Soukar\QAlert\Services;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Soukar\QAlert\Facades\QAlert;

class LogHandler extends AbstractProcessingHandler{



    protected function write(LogRecord $record): void
    {
        $message = $record->formatted;

        QAlert::sendMessageToAllChannels($message);

    }
}
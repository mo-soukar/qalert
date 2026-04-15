<?php

namespace Soukar\QAlert\Services;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Soukar\QAlert\Facades\QAlert;

class LogHandler extends AbstractProcessingHandler{


    public function __construct($level = \Monolog\Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }
    protected function write(LogRecord $record): void
    {
        $message = $record->formatted;

        QAlert::sendMessageToAllChannels($message);

    }
}
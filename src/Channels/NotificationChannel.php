<?php

namespace Soukar\QAlert\Channels;
interface NotificationChannel {
    public function send(string $message, array $payload=[]);
}
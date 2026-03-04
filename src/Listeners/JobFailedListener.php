<?php

namespace Soukar\QAlert\Listeners;

use Illuminate\Support\Facades\Log;
use Soukar\QAlert\Facades\QAlert;

class JobFailedListener
{
    public function __construct() {}

    public function handle($event)
    {
        QAlert::handle($event);
    }
}

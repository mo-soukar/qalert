<?php

namespace Soukar\QAlert\Facades;

use Illuminate\Support\Facades\Facade;

class QAlert extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'qalert-manager';
    }
}
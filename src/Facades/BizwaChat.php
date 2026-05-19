<?php

namespace BizwaChat\LaravelNotification\Facades;

use Illuminate\Support\Facades\Facade;

class BizwaChat extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bizwachat';
    }
}
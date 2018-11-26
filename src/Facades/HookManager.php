<?php

namespace Pikart\LaravelHooks\Facades;

use Illuminate\Support\Facades\Facade;

class HookManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pikart.laravel-hook';
    }
}
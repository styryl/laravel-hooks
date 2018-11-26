<?php
namespace Pikart\LaravelHooks;
use Pikart\LaravelHooks\Contracts\Hook as HookContract;

abstract class Hook implements HookContract
{
    /**
     * Default singleton
     *
     * @var bool
     */
    protected $isSingleton = true;

    /**
     * Checks if hook is singleton.
     *
     * @return bool
     */
    public function isSingleton(): bool
    {
        return $this->isSingleton;
    }
}
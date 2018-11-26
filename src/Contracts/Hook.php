<?php
namespace Pikart\LaravelHooks\Contracts;

interface Hook
{
    /**
     * Checks if hook is singleton
     *
     * @return bool
     */
    public function isSingleton() : bool;

    /**
     * Handles execution of hook
     *
     * @param array $args
     * @return null|string
     */
    public function handle( array $args ) : ?string;
}
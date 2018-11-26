<?php

if (!function_exists('hook'))
{
    function hook( string $contract = null, array $args = [] )
    {
        if( $contract )
        {
            return app()->make( 'pikart.laravel-hook')->hook( $contract, $args );
        }

        return app()->make( 'pikart.laravel-hook');
    }
}
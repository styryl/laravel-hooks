<?php

namespace Pikart\LaravelHooks;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;


class HookServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot( Router $router )
    {
        $this->publishes([
            __DIR__.'/../config/hook.php' => config_path('hook.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        require __DIR__.'/helper.php';

        $this->mergeConfigFrom(
            __DIR__.'/../config/hook.php', 'hook'
        );

        $this->app->singleton('pikart.laravel-hook', function($app) {
            return new HookManager( $app );
        });
    }

}

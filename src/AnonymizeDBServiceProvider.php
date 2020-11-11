<?php

namespace Joonas1234\LaravelAnonymizeDB;

use Illuminate\Support\ServiceProvider;
use Joonas1234\LaravelAnonymizeDB\Commands\DBAnonymizeCommand;

class AnonymizeDBServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('anonymize-db.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'anonymize-db');

        $this->app->bind('command.db:anonymize', DBAnonymizeCommand::class);

        $this->commands([
            'command.db:anonymize'
        ]);
    }
}
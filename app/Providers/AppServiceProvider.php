<?php

namespace App\Providers;

use App\Customize\Database\Query\Grammars\MySqlGrammar;
use App\Customize\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Console\ModelMakeCommand as BaseModelMakeCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->resolving(
            'db',
            function(DatabaseManager $databaseManager, Container $app){
                $databaseManager->extend(
                    'mysql',
                    function($config, $name) use ($app){
                        $connection = $app['db.factory']->make($config, $name);
                        $connection->setQueryGrammar($connection->withTablePrefix(new MySqlGrammar()));
                        return $connection;
                    }
                );
            }
        );
        $this->app->extend('command.model.make', function (BaseModelMakeCommand $modelMakeCommand, Container $app) {
            return new ModelMakeCommand($app['files']);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

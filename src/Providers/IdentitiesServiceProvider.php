<?php

namespace Oneofftech\Identities\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Oneofftech\Identities\Encryption\Encrypter;
use Oneofftech\Identities\View\Components\IdentityLink;
use Oneofftech\Identities\Console\Commands\ScaffoldAuthenticationControllers;

class IdentitiesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewComponentsAs('oneofftech', [
            IdentityLink::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ScaffoldAuthenticationControllers::class,
            ]);
            $this->publishes([
                __DIR__.'/../../config/identities.php' => config_path('identities.php'),
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/identities.php',
            'identities'
        );

        $this->app->singleton(Encrypter::class, function ($app) {
            $config = $app->make('config')->get('identities');
            $app_config = $app->make('config')->get('app');

            if (Str::startsWith($key = $config['key'], 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            return new Encrypter($key, $app_config['cipher']);
        });
    }

    // /**
    //  * Determine if the provider is deferred.
    //  *
    //  * @return bool
    //  */
    // public function isDeferred()
    // {
    //     return true;
    // }
}

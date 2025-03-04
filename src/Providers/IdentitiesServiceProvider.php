<?php

namespace Oneofftech\Identities\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Oneofftech\Identities\Console\Commands\ScaffoldAuthenticationControllers;
use Oneofftech\Identities\Encryption\Encrypter;
use Oneofftech\Identities\IdentitiesManager;
use Oneofftech\Identities\View\Components\IdentityLink;

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

            $key = $config['key'] ?? $app_config['key'];

            if (Str::startsWith($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            return new Encrypter($key, $app_config['cipher']);
        });

        $this->app->singleton(IdentitiesManager::class, function ($app) {
            return new IdentitiesManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [IdentitiesManager::class];
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

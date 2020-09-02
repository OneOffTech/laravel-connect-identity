<?php

namespace Oneofftech\Identities\Providers;

use Illuminate\Support\ServiceProvider;
use Oneofftech\Identities\Console\Commands\ScaffoldAuthenticationControllers;

class IdentitiesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScaffoldAuthenticationControllers::class,
            ]);
        }
    }

    public function register()
    {

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


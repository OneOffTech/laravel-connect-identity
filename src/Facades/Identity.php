<?php

namespace Oneofftech\Identities\Facades;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Facade;
use Oneofftech\Identities\IdentitiesManager;
use SocialiteProviders\Dropbox\DropboxExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\GitLab\GitLabExtendSocialite;

/**
 * @see \Oneofftech\Identities\IdentitiesManager
 */
class Identity extends Facade
{
    /**
     * The user model that should be used.
     *
     * @var string
     */
    public static $appNamespace = 'App';

    /**
     * The user model that should be used.
     *
     * @var string
     */
    public static $userModel = 'App\\User';

    /**
     * The identity model that should be used.
     *
     * @var string
     */
    public static $identityModel = 'App\\Identity';

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return IdentitiesManager::class;
    }

    /**
     * Register the routes for handling login, registration
     * and identity management for an application.
     *
     * @return void
     */
    public static function routes()
    {
        $router = static::$app->make('router');

        $namespace = '\\'.rtrim(self::$appNamespace, '\\');

        $router->get('login-via/{provider}', "$namespace\Http\Controllers\Identities\Auth\LoginController@redirect")
            ->name("oneofftech::login.provider");
        $router->get('login-via/{provider}/callback', "$namespace\Http\Controllers\Identities\Auth\LoginController@login")
            ->name("oneofftech::login.callback");
        
        $router->get('register-via/{provider}', "$namespace\Http\Controllers\Identities\Auth\RegisterController@redirect")
            ->name("oneofftech::register.provider");
        $router->get('register-via/{provider}/callback', "$namespace\Http\Controllers\Identities\Auth\RegisterController@register")
            ->name("oneofftech::register.callback");
        
        $router->get('connect-via/{provider}', "$namespace\Http\Controllers\Identities\Auth\ConnectController@redirect")
            ->name("oneofftech::connect.provider");
        $router->get('connect-via/{provider}/callback', "$namespace\Http\Controllers\Identities\Auth\ConnectController@connect")
            ->name("oneofftech::connect.callback");
    }

    /**
     * Register the events listeners required to handle
     * Socialite extensions authentication/authorization
     * flows.
     *
     * @return void
     */
    public static function events()
    {
        Event::listen(SocialiteWasCalled::class, GitLabExtendSocialite::class);
        Event::listen(SocialiteWasCalled::class, DropboxExtendSocialite::class);
    }

    /**
     * Find a user instance by the given ID.
     *
     * @param  string  $id
     */
    public static function findUserByIdOrFail(string $id)
    {
        return static::newUserModel()->where('id', $id)->firstOrFail();
    }

    /**
     * Find a user instance by the given provider or fail.
     *
     * @param  string  $provider
     * @param  string  $id
     * @return mixed
     */
    public static function findUserByIdentity(string $provider, string $id)
    {
        return static::newUserModel()
            ->whereHas('identities', function ($query) use ($provider, $id) {
                $query->where('provider', $provider)
                    ->where('provider_id', $id);
            })
            ->firstOrFail();
    }

    /**
     * Get the name of the user model used by the application.
     *
     * @return string
     */
    public static function userModel()
    {
        return static::$userModel;
    }

    /**
     * Get a new instance of the user model.
     *
     * @return mixed
     */
    public static function newUserModel()
    {
        $model = static::userModel();

        return new $model;
    }

    /**
     * Specify the user model that should be used.
     *
     * @param  string  $model
     * @return static
     */
    public static function useUserModel(string $model)
    {
        static::$userModel = $model;

        return new static;
    }

    /**
     * Get the name of the identity model used by the application.
     *
     * @return string
     */
    public static function identityModel()
    {
        return static::$identityModel;
    }

    /**
     * Get a new instance of the identity model.
     *
     * @return mixed
     */
    public static function newIdentityModel()
    {
        $model = static::identityModel();

        return new $model;
    }

    /**
     * Specify the identity model that should be used.
     *
     * @param  string  $model
     * @return static
     */
    public static function useIdentityModel(string $model)
    {
        static::$identityModel = $model;

        return new static;
    }

    /**
     * Specify the application namespace that should be used.
     *
     * @param  string  $model
     * @return static
     */
    public static function useNamespace(string $namespace)
    {
        static::$appNamespace = $namespace;

        return new static;
    }
    
    /**
     * Get the configured namespace
     *
     * @return string
     */
    public static function namespace()
    {
        return static::$appNamespace;
    }
}

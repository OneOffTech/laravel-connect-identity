<?php

namespace Oneofftech\Identities\Facades;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Facade;
use Oneofftech\Identities\IdentitiesManager;
use SocialiteProviders\Dropbox\DropboxExtendSocialite;
use SocialiteProviders\GitLab\GitLabExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
    public static $userModel = 'App\\Models\\User';

    /**
     * The identity model that should be used.
     *
     * @var string
     */
    public static $identityModel = 'App\\Models\\Identity';

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
        /**
         * @var \Illuminate\Routing\Router
         */
        $router = static::$app->make('router');

        $namespace = '\\'.rtrim(self::$appNamespace, '\\');

        $router
            ->middleware('web')
            ->group(function ($groupRouter) use ($namespace) {
                $groupRouter->match(['get', 'post'], 'login-via/{provider}', "$namespace\Http\Controllers\Identities\Auth\LoginController@redirect")
                    ->name('oneofftech::login.provider');
                $groupRouter->get('login-via/{provider}/callback', "$namespace\Http\Controllers\Identities\Auth\LoginController@login")
                    ->name('oneofftech::login.callback');

                $groupRouter->match(['get', 'post'], 'register-via/{provider}', "$namespace\Http\Controllers\Identities\Auth\RegisterController@redirect")
                    ->name('oneofftech::register.provider');
                $groupRouter->get('register-via/{provider}/callback', "$namespace\Http\Controllers\Identities\Auth\RegisterController@register")
                    ->name('oneofftech::register.callback');

                $groupRouter->match(['get', 'post'], 'connect-via/{provider}', "$namespace\Http\Controllers\Identities\Auth\ConnectController@redirect")
                    ->middleware('auth')
                    ->name('oneofftech::connect.provider');
                $groupRouter->get('connect-via/{provider}/callback', "$namespace\Http\Controllers\Identities\Auth\ConnectController@connect")
                    ->middleware('auth')
                    ->name('oneofftech::connect.callback');
            });

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
     */
    public static function findUserByIdOrFail(string $id)
    {
        $model = static::newUserModel();

        return $model->where($model->getKeyName(), $id)->firstOrFail();
    }

    /**
     * Find a user instance by the given provider or fail.
     *
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

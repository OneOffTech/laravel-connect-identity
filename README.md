
# Laravel Connect Identity

Add registration and log in to you application via third party identity providers (e.g. Gitlab, Facebook, ...).

The package is compatible with the [Laravel Socialite](https://laravel.com/docs/8.x/socialite) providers 
as well as the community driven [Socialite Providers](https://socialiteproviders.com/) website.

> Requires **Laravel >= 7.20** and **PHP >= 7.2**

> **The package is currently a Work In Progress.** The api might change without notice so it is not yet 
suitable for production environments.

## Installation

You can install this package via Composer by running this command in your terminal in the root of your project:

```bash
composer require oneofftech/laravel-connect-identity
```

> The service provider `Oneofftech\Identities\IdentitiesServiceProvider::class` 
> is automatically registered as part of the Laravel service discovery

## Configuration

Scaffold the controllers, migrations and models.

```
php artisan ui:identities
```

Add the WithIdentities trait to your User model to use the `identities` relationship.

```php
// ...

use Oneofftech\Identities\WithIdentities;

class User extends Authenticatable
{
    use Notifiable, WithIdentities;

    // ...
}
```


Register the events to your events service provider.

> This will register the SocialiteWasCalled event for the Gitlab and Dropbox 
providers that are included by default. If you are not using those providers
this step is optional.

```
\Oneofftech\Identities\Facades\Identity::events();
```

    public function boot()
    {
        parent::boot();

        \Oneofftech\Identities\Facades\Identity::events();
    }


Inserire i valori nei services secondo le specifiche di Laravel Socialite


```php
    'gitlab' => [
        'client_id' => env('GITLAB_CLIENT_ID'),
        'client_secret' => env('GITLAB_CLIENT_SECRET'),
        'redirect' => null, // set in the controller no need to specify
        'instance_uri' => env('GITLAB_INSTANCE_URI', 'https://gitlab.com/')
    ],
```


### Using a personalized application namespace

If you are using a custom application namespace instead of the default `App`, 
you need to tell which namespace and models to use.

To do so add the following lines in your AppServiceProvider;

```php

use Oneofftech\Identities\Facades\Identity;

class AppServiceProvider extends ServiceProvider
{

    public function boot()
    {
        Identity::useNamespace("KBox\\");
        Identity::useIdentityModel("KBox\\Identity");
        Identity::useUserModel("KBox\\User");

        // ...
    }
}
```

The `ui:identities` command is namespace aware.


## Basic Usage

...

## License

Laravel Connect Identity is licensed under the [MIT license](./LICENSE).

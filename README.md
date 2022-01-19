
# Connect Identity for Laravel

![CI](https://github.com/OneOffTech/laravel-connect-identity/workflows/CI/badge.svg)

Add registration and log in to you application via third party identity providers (e.g. Gitlab, Facebook, ...).

While the package provides controllers, models, migrations and routes to handle registration and login actions
it does not dictate how the user interface should look and how to validate users' data. It does however
provide a starting point that you can customize based on your needs.

The package is compatible with [Laravel Socialite](https://laravel.com/docs/socialite) providers 
as well as the community driven [Socialite Providers](https://socialiteproviders.com/).

**features**

- Handle user registration via third party providers;
- Handle user log in via third party providers;
- Allow existing user to link a third party identity;
- Customizable controllers, migration and models that will live in your application namespace;
- Save identity and token inside the database, using
  [encryption and pseudoanonimization](#how-data-is-stored-in-the-database);
- Provide login/register/connect button as Blade component;
- Support all [Laravel Socialite](https://laravel.com/docs/socialite)
  and [Socialite Providers](https://socialiteproviders.com/);
- Add custom providers.

**requirements**

`oneofftech/laravel-connect-identity` requires **Laravel >= 8.0** and **PHP >= 7.3**.

> **The package is currently a Work In Progress.** The api might change without notice so it is not yet 
suitable for production environments.

## Getting started

### Installation

You can install this package via Composer by running this command in your terminal in the root of your project:

```bash
composer require oneofftech/laravel-connect-identity
```

> The service provider `Oneofftech\Identities\IdentitiesServiceProvider::class` 
> is automatically registered as part of the Laravel service discovery.

### Generate migrations, controllers and models

The package provides the login and registration features via traits.
Once the `oneofftech/laravel-connect-identity` package has been installed, 
you can generate the controllers, models and migrations scaffolding using the 
`ui:identities` Artisan command:

```
php artisan ui:identities
```

Now you can add the `WithIdentities` trait to your `User` model. This is required
to use the `identities` relationship required during the registration/login process.

```php
// ...

use Oneofftech\Identities\WithIdentities;

class User extends Authenticatable
{
    use WithIdentities;

    // ...
}
```

> If your application has a different namespace than `App` please refer 
to [Using a personalized application namespace](#using-a-personalized-application-namespace)
for additional required setup actions.

### Configure the Socialite providers

Before using an identity provider, e.g. `facebook`, `google`, `github`, `gitlab`,
configure the required options inside the `services` configuration file.

> To see the driver specific configuration please refer to 
[Laravel's documentation](https://laravel.com/docs/socialite) or the
[Socialite Providers documentation](https://socialiteproviders.com/).

> The `redirect` url configuration is not required as the redirect url is set automatically.

```php
    'gitlab' => [
        'client_id' => env('GITLAB_CLIENT_ID'),
        'client_secret' => env('GITLAB_CLIENT_SECRET'),
        'redirect' => null, // set in the controller no need to specify
        'instance_uri' => env('GITLAB_INSTANCE_URI', 'https://gitlab.com/')
    ],
```

If you are using one of the community maintained [Socialite Providers](https://socialiteproviders.com/)
remember to register their events in your `EventsServiceProvider`.

If you are not using those providers this step is optional.

`oneofftech/laravel-connect-identity` provides out-of-the-box support for the `gitlab` 
and `dropbox` driver. If you are using those two you might add the following call to 
your `EventsServiceProvider`.

```php

public function boot()
{
    parent::boot();

    \Oneofftech\Identities\Facades\Identity::events();
}
```

This will register the `SocialiteWasCalled` event for the Gitlab and Dropbox 
providers that are included by default. 


### Include the login and register buttons

`oneofftech/laravel-connect-identity` does not dictate your User Interface preferences, 
however we provide a Blade Component to quickly add login and register links/buttons.

```html
<x-oneofftech-identity-link 
    action="register" 
    provider="gitlab" 
    class="button button--primary" />
```

The available `action`s are `login`, `connect` and `register`. The `provider` refers to what
identity provider to use, the name of the provider is the same as the Socialite
providers' name. See [Blade components](https://laravel.com/docs/blade#components) for more.

In case of errors, mainly connected to validation, you can catch those by looking at 
the used provider key in the Laravel default ErrorBag.

```html
@error('gitlab')
    <span class="field-error" role="alert">
        {{ $message }}
    </span>
@enderror
```

## Digging Deeper

### Using a personalized application namespace

While the `ui:identities` command is namespace aware some of the runtime configuration
is not.

If you are using a custom application namespace instead of the default `App`, 
you need to tell which namespace and models to use.

To do so add the following lines in your `AppServiceProvider`;

```php

use Oneofftech\Identities\Facades\Identity;

class AppServiceProvider extends ServiceProvider
{

    public function boot()
    {
        Identity::useNamespace("My\\Namespace\\");
        Identity::useIdentityModel("My\\Namespace\\Identity");
        Identity::useUserModel("My\\Namespace\\User");

        // ...
    }
}
```

### Passing additional data to the registration

Sometimes you will need additional parameters do create a user after the authorization 
process on the third party service. To do so you can add as many parameters in the
request made to the register route that redirects to the third party service.

By default additional request parameters are guarded and so you have to explicitly
tell their name. You can do this by defining an `attributes` property or an
`attributes` method on the `RegisterController` that returns an array of strings
that represent the name of the allowed parameters.

```php
protected $attributes = ['name'];

protected function attributes()
{
    return  ['name'];
}
```

The additional attributes will then passed to the `validator(array $data)` and
`create(array $data)` function defined within the `RegisterController`.

If you are using the provided `IdentityLink` Blade component the data should
be specified as associative array inside the `parameters` property.

```html
<x-oneofftech-identity-link 
    action="register" 
    provider="gitlab" 
    :parameters="$arrayOfAdditionalParameters"
    class="button button--primary" />
```

Where `$arrayOfAdditionalParameters` is an associative array, e.g. `['invite' => 'token_value']`.

### How data is stored in the database

Whenever possible data is stored encrypted or in a pseudo-anonymized form.

Encryption works by using the [Laravel's Encryption](https://laravel.com/docs/encryption) and the configured
application key (i.e. `APP_KEY`). If you want to use a different key use the `IDENTITY_KEY` environment variable, the 
used cipher will be the same as configured in `app.cipher`.

The pseudo-anonymized values are stored as hashes of the original data.

Here is how sensible data is stored:

| data                                  | technique         |
|---------------------------------------|-------------------|
| identifier within third party service | pseudo-anonymized |
| authentication token                  | encrypted         |
| refresh token                         | encrypted         |

> If you need to [rotate the APP_KEY](https://divinglaravel.com/app_key-is-a-secret-heres-what-its-used-for-how-you-can-rotate-it)
specify your old key inside `OLD_IDENTITY_KEY` to be able to still read encrypted values.

> **Warning** as of now no automated job is available for re-encrypting data with the new key. 
This operation happens during registration or while connecting an identity as part of the token update.


## Contributing

Thank you for considering contributing to the Connect Identity for Laravel! 
You can find how to get started in our [contribution guide](./CONTRIBUTING.md).

## Security Vulnerabilities

Please review our [security policy](./SECURITY.md) on how to report security vulnerabilities.

## License

Laravel Connect Identity is licensed under the [MIT license](./LICENSE).

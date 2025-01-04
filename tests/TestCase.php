<?php

namespace Tests;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use Laravel\Socialite\SocialiteServiceProvider;
use Oneofftech\Identities\Facades\Identity as IdentityFacade;
use Oneofftech\Identities\Providers\IdentitiesServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use ReflectionFunction;
use SocialiteProviders\Dropbox\DropboxExtendSocialite;
use SocialiteProviders\GitLab\GitLabExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Tests\Fixtures\Concern\UseTestFixtures;
use Tests\Fixtures\User;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSession($this->app);

        $this->activateSocialiteExtensions();
    }

    // protected function setUpTraits()
    // {
    //     parent::setUpTraits();

    //     $uses = \array_flip(\class_uses_recursive(static::class));

    //     if (isset($uses[UseTestFixtures::class])) {
    //         $this->useTestFixtures();
    //     }

    //     return $uses;
    // }

    /**
     * Define environment setup.
     *
     * - Sqlite in memory database
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('services.gitlab', [
            'client_id' => 'aaa',
            'client_secret' => 'bbb',
            'redirect' => null,
            'instance_uri' => 'https://gitlab.com/',
        ]);
        config()->set('services.dropbox', [
            'client_id' => 'aaa',
            'client_secret' => 'bbb',
            'redirect' => null,
        ]);

        $key = Str::random(32);
        config()->set('app.key', 'base64:'.base64_encode($key));
        config()->set('app.cipher', 'AES-256-CBC');
        config()->set('identities.key', 'base64:'.base64_encode($key));

        IdentityFacade::useNamespace('App');
        IdentityFacade::useIdentityModel('App\\Identity');
        IdentityFacade::useUserModel('App\\User');
    }

    /**
     * Loads the service provider during the tests
     */
    protected function getPackageProviders($app)
    {
        return [
            SocialiteServiceProvider::class,
            \SocialiteProviders\Manager\ServiceProvider::class,
            IdentitiesServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();

        $this->loadMigrationsFrom(
            __DIR__.'/../stubs/migrations'
        );
    }

    protected function setUpSession()
    {
        $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');
        $kernel->pushMiddleware('Illuminate\Session\Middleware\StartSession');
    }

    protected function activateSocialiteExtensions()
    {
        $socialiteWasCalled = $this->app->make(SocialiteWasCalled::class);

        $this->app->make(GitLabExtendSocialite::class)->handle($socialiteWasCalled);
        $this->app->make(DropboxExtendSocialite::class)->handle($socialiteWasCalled);
    }

    public function assertListenerIsAttachedToEvent($listener, $event)
    {
        $dispatcher = app(Dispatcher::class);

        foreach ($dispatcher->getListeners(is_object($event) ? get_class($event) : $event) as $listenerClosure) {
            $reflection = new ReflectionFunction($listenerClosure);
            $listenerClass = $reflection->getStaticVariables()['listener'];

            if ($listenerClass === $listener) {
                $this->assertTrue(true);

                return;
            }
        }

        $this->assertTrue(false, sprintf('Event %s does not have the %s listener attached to it', $event, $listener));
    }

    public function createUser($data = [])
    {
        return tap((new User), function ($u) use ($data) {
            $u->forceFill(array_merge([
                'email' => 'user@local.com',
                'name' => 'User',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'remember_token' => Str::random(10),
            ], $data))->save();
        });
    }
}

<?php

namespace Tests;

use ReflectionFunction;
use Illuminate\Support\Str;
use Illuminate\Events\Dispatcher;
use Laravel\Socialite\SocialiteServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Oneofftech\Identities\Facades\Identity as IdentityFacade;
use Oneofftech\Identities\Providers\IdentitiesServiceProvider;
use SocialiteProviders\Dropbox\DropboxExtendSocialite;
use SocialiteProviders\GitLab\GitLabExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

abstract class TestCase extends BaseTestCase
{

    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->activateSocialiteExtensions();
    }

    /**
     * Define environment setup.
     *
     * - Sqlite in memory database
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('services.gitlab', [
            'client_id' => 'aaa',
            'client_secret' => 'bbb',
            'redirect' => null,
            'instance_uri' => 'https://gitlab.com/'
        ]);
        $app['config']->set('services.dropbox', [
            'client_id' => 'aaa',
            'client_secret' => 'bbb',
            'redirect' => null,
        ]);

        $key = Str::random(32);
        $app['config']->set('app.key', 'base64:'.base64_encode($key));
        $app['config']->set('app.cipher', 'AES-256-CBC');
        $app['config']->set('identities.key', 'base64:'.base64_encode($key));
    }
    
    /**
     * Loads the service provider during the tests
     */
    protected function getPackageProviders($app)
    {
        return [
            SocialiteServiceProvider::class,
            \SocialiteProviders\Manager\ServiceProvider::class,
            IdentitiesServiceProvider::class
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $this->loadLaravelMigrations();

        $this->loadMigrationsFrom(__DIR__.'/../stubs/migrations');

        IdentityFacade::useNamespace("App");
        IdentityFacade::useIdentityModel("App\\Identity");
        IdentityFacade::useUserModel("App\\User");
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
}

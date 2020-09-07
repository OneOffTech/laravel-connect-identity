<?php

namespace Tests;

use ReflectionFunction;
use Illuminate\Events\Dispatcher;
use Laravel\Socialite\SocialiteServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Oneofftech\Identities\Providers\IdentitiesServiceProvider;

abstract class TestCase extends BaseTestCase
{

    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Your code here
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
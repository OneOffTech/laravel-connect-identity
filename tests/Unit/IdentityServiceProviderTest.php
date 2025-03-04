<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Laravel\Socialite\Two\FacebookProvider;
use Oneofftech\Identities\Facades\Identity;
use Oneofftech\Identities\IdentitiesManager;
use Oneofftech\Identities\Providers\IdentitiesServiceProvider;
use Orchestra\Testbench\Attributes\WithMigration;
use SocialiteProviders\Dropbox\Provider as DropboxDriver;
use SocialiteProviders\GitLab\GitLabExtendSocialite;
use SocialiteProviders\GitLab\Provider as GitlabSocialiteProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Tests\TestCase;

#[WithMigration]
class IdentityServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_driver_cannot_not_configured()
    {
        $factory = $this->app->make(IdentitiesManager::class);

        $this->expectException(InvalidArgumentException::class);

        $factory->redirect();
    }

    public function test_it_can_instantiate_the_gitlab_driver()
    {
        $factory = $this->app->make(IdentitiesManager::class);

        $provider = $factory->driver('gitlab');

        $this->assertInstanceOf(GitlabSocialiteProvider::class, $provider);
    }

    public function test_it_can_instantiate_the_facebook_driver()
    {
        $this->app['config']->set('services.facebook', [
            'client_id' => 'aaa',
            'client_secret' => 'bbb',
            'redirect' => null,
        ]);

        $factory = $this->app->make(IdentitiesManager::class);

        $provider = $factory->driver('facebook');

        $this->assertInstanceOf(FacebookProvider::class, $provider);
    }

    public function test_non_existing_provider_throws()
    {
        $factory = $this->app->make(IdentitiesManager::class);

        $this->expectException(InvalidArgumentException::class);

        $provider = $factory->driver('slurpbook');
    }

    public function test_it_can_instantiate_the_dropbox_driver()
    {
        $factory = $this->app->make(IdentitiesManager::class);

        $provider = $factory->driver('dropbox');

        $this->assertInstanceOf(DropboxDriver::class, $provider);
    }

    public function test_routes_are_registered()
    {
        Identity::routes();

        /**
         * @var \Illuminate\Routing\Router
         */
        $router = tap($this->app->make('router'), function ($r) {
            // refresh the route name cache
            $r->getRoutes()->refreshNameLookups();
        });

        $this->assertTrue($router->has('oneofftech::login.provider'));
        $this->assertTrue($router->has('oneofftech::login.callback'));
        $this->assertTrue($router->has('oneofftech::register.provider'));
        $this->assertTrue($router->has('oneofftech::register.callback'));
        $this->assertTrue($router->has('oneofftech::connect.provider'));
        $this->assertTrue($router->has('oneofftech::connect.callback'));

        $routes = collect($router->getRoutes()->getRoutes())->map(function ($r) {
            return implode(',', $r->methods()).':'.$r->getActionName();
        });

        $this->assertContains('GET,POST,HEAD:\App\Http\Controllers\Identities\Auth\LoginController@redirect', $routes);
        $this->assertContains('GET,HEAD:\App\Http\Controllers\Identities\Auth\LoginController@login', $routes);
        $this->assertContains('GET,POST,HEAD:\App\Http\Controllers\Identities\Auth\RegisterController@redirect', $routes);
        $this->assertContains('GET,HEAD:\App\Http\Controllers\Identities\Auth\RegisterController@register', $routes);
        $this->assertContains('GET,POST,HEAD:\App\Http\Controllers\Identities\Auth\ConnectController@redirect', $routes);
        $this->assertContains('GET,HEAD:\App\Http\Controllers\Identities\Auth\ConnectController@connect', $routes);
    }

    public function test_events_are_registered()
    {
        Identity::events();

        $this->assertListenerIsAttachedToEvent(GitLabExtendSocialite::class, SocialiteWasCalled::class);
    }

    public function test_facade_return_manager_instance()
    {
        $this->assertInstanceOf(IdentitiesManager::class, Identity::getFacadeRoot());
    }

    public function test_provider_lists_provided_services()
    {
        $provides = (new IdentitiesServiceProvider($this->app))->provides();

        $this->assertEquals([IdentitiesManager::class], $provides);
    }
}

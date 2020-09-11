<?php

namespace Tests\Unit;

use Tests\TestCase;
use Laravel\Socialite\Two\GitlabProvider;
use Oneofftech\Identities\Facades\Identity;
use Oneofftech\Identities\IdentitiesManager;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\GitLab\GitLabExtendSocialite;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class IdentityServiceProviderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_can_instantiate_the_gitlab_driver()
    {
        $factory = $this->app->make(IdentitiesManager::class);

        $provider = $factory->driver('gitlab');

        $this->assertInstanceOf(GitlabProvider::class, $provider);
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

        $routes = collect($router->getRoutes()->getRoutes())->map(function ($r) {
            return $r->getActionName();
        });

        $this->assertContains('\App\Http\Controllers\Identities\Auth\LoginController@redirect', $routes);
        $this->assertContains('\App\Http\Controllers\Identities\Auth\LoginController@login', $routes);
        $this->assertContains('\App\Http\Controllers\Identities\Auth\RegisterController@redirect', $routes);
        $this->assertContains('\App\Http\Controllers\Identities\Auth\RegisterController@register', $routes);
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
}

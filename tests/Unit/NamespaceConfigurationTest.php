<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Oneofftech\Identities\Facades\Identity as IdentityFacade;
use Tests\TestCase;

class NamespaceConfigurationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_default_app_namespace()
    {
        $this->assertEquals('App', IdentityFacade::namespace());
    }

    public function test_namespace_can_be_customized()
    {
        IdentityFacade::useNamespace('Test\\Fixtures');

        $this->assertEquals('Test\\Fixtures', IdentityFacade::namespace());
    }

    public function test_routes_registered_when_using_custom_namespace()
    {
        IdentityFacade::useNamespace('Test\\Fixtures');

        IdentityFacade::routes();

        /**
         * @var \Illuminate\Routing\Router
         */
        $router = tap($this->app->make('router'), function ($r) {
            // refresh the route name cache
            $r->getRoutes()->refreshNameLookups();
        });

        $routes = collect($router->getRoutes()->getRoutes())->map(function ($r) {
            return $r->getActionName();
        });

        $this->assertTrue($router->has('oneofftech::login.provider'));
        $this->assertTrue($router->has('oneofftech::login.callback'));
        $this->assertTrue($router->has('oneofftech::register.provider'));
        $this->assertTrue($router->has('oneofftech::register.callback'));

        $this->assertContains('\Test\Fixtures\Http\Controllers\Identities\Auth\LoginController@redirect', $routes);
        $this->assertContains('\Test\Fixtures\Http\Controllers\Identities\Auth\LoginController@login', $routes);
        $this->assertContains('\Test\Fixtures\Http\Controllers\Identities\Auth\RegisterController@redirect', $routes);
        $this->assertContains('\Test\Fixtures\Http\Controllers\Identities\Auth\RegisterController@register', $routes);
    }
}

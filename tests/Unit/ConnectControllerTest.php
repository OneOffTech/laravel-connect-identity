<?php

namespace Tests\Unit;

use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Oneofftech\Identities\Facades\Identity as IdentityFacade;
use Tests\Fixtures\User;
use Illuminate\Support\Str;

class ConnectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_to_provider_require_authentication()
    {
        IdentityFacade::useNamespace('Tests\\Fixtures');
        IdentityFacade::routes();

        $router = $this->app->make('router');

        $router->get('login', function () {
        })->name('login');

        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthenticated');

        $this->get(route('oneofftech::connect.provider', ['provider' => 'gitlab']));
    }

    public function test_redirect_to_provider()
    {
        IdentityFacade::useNamespace('Tests\\Fixtures');
        IdentityFacade::routes();

        $user = tap((new User()), function ($u) {
            $u->forceFill([
                'email' => 'user@local.local',
                'name' => 'User',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'remember_token' => Str::random(10),
            ])->save();
        });

        $response = $this->actingAs($user)
            ->get(route('oneofftech::connect.provider', ['provider' => 'gitlab']));

        $response->assertRedirect();
        
        $location = urldecode($response->headers->get('Location'));

        $this->assertStringContainsString('gitlab.com', $location);
        $this->assertStringContainsString(route('oneofftech::connect.callback', ['provider' => 'gitlab']), $location);
    }
}

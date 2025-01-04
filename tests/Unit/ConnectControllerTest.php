<?php

namespace Tests\Unit;

use Mockery;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\Fixtures\User;
use Illuminate\Support\Str;
use SocialiteProviders\GitLab\Provider;
use Tests\Fixtures\Concern\UseTestFixtures;
use Illuminate\Auth\AuthenticationException;
use Oneofftech\Identities\Facades\IdentityCrypt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SocialiteProviders\Manager\OAuth2\User as OauthUser;
use Oneofftech\Identities\Facades\Identity as IdentityFacade;

class ConnectControllerTest extends TestCase
{
    use RefreshDatabase, UseTestFixtures;

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

    public function test_connect_creates_identity()
    {
        $user = $this->createUser();

        $this->withoutExceptionHandling();

        $driverMock = Mockery::mock(Provider::class)->makePartial();

        Carbon::setTestNow(Carbon::create(2020, 11, 12, 10, 20));

        $oauthFakeUser = (new OauthUser())->map([
            'id'       => 'U1',
            'nickname' => 'User',
            'name'     => 'User',
            'email'    => 'user@local.com',
            'avatar'   => 'https://gitlab.com',
            'token'   => 'T2',
            'refreshToken' => 'RT2',
            'expiresIn' => Carbon::SECONDS_PER_MINUTE,
        ]);
        
        $driverMock->shouldReceive('user')->andReturn($oauthFakeUser);

        $driverMock->shouldReceive('redirectUrl')->andReturn($driverMock);

        IdentityFacade::shouldReceive('driver')->with('gitlab')->andReturn($driverMock);

        $response = $this->actingAs($user)
            ->get(route('oneofftech::connect.callback', ['provider' => 'gitlab']));

        $response->assertRedirect('http://localhost/home');

        $updatedIdentity = $user->identities->first();

        $this->assertEquals(IdentityCrypt::hash('U1'), $updatedIdentity->provider_id);
        $this->assertEquals('gitlab', $updatedIdentity->provider);
        $this->assertEquals(Carbon::create(2020, 11, 12, 10, 21), $updatedIdentity->expires_at);
        $this->assertEquals('T2', IdentityCrypt::decryptString($updatedIdentity->token));
        $this->assertEquals('RT2', IdentityCrypt::decryptString($updatedIdentity->refresh_token));
        $this->assertFalse($updatedIdentity->registration);
    }
    
    public function test_connect_updates_existing_identity()
    {
        $user = $this->createUser();

        $identity = $user->identities()->create([
            'provider'=> 'gitlab',
            'provider_id'=> IdentityCrypt::hash('U1'),
            'token'=> 'T1',
            'refresh_token'=> null,
            'expires_at'=> null,
            'registration' => true,
        ]);

        $this->withoutExceptionHandling();

        $driverMock = Mockery::mock(Provider::class)->makePartial();

        Carbon::setTestNow(Carbon::create(2020, 11, 12, 10, 20));

        $oauthFakeUser = (new OauthUser())->map([
            'id'       => 'U1',
            'nickname' => 'User',
            'name'     => 'User',
            'email'    => 'user@local.com',
            'avatar'   => 'https://gitlab.com',
            'token'   => 'T2',
            'refreshToken' => 'RT2',
            'expiresIn' => Carbon::SECONDS_PER_MINUTE,
        ]);
        
        $driverMock->shouldReceive('user')->andReturn($oauthFakeUser);

        $driverMock->shouldReceive('redirectUrl')->andReturn($driverMock);

        IdentityFacade::shouldReceive('driver')->with('gitlab')->andReturn($driverMock);

        $response = $this->actingAs($user)
            ->get(route('oneofftech::connect.callback', ['provider' => 'gitlab']));

        $response->assertRedirect('http://localhost/home');

        $updatedIdentity = $user->identities->first();

        $this->assertEquals($identity->provider_id, $updatedIdentity->provider_id);
        $this->assertEquals('gitlab', $updatedIdentity->provider);
        $this->assertEquals(Carbon::create(2020, 11, 12, 10, 21), $updatedIdentity->expires_at);
        $this->assertEquals('T2', IdentityCrypt::decryptString($updatedIdentity->token));
        $this->assertEquals('RT2', IdentityCrypt::decryptString($updatedIdentity->refresh_token));
    }
}

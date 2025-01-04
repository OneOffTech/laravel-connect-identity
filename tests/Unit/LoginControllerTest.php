<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Oneofftech\Identities\Facades\Identity as IdentityFacade;
use Oneofftech\Identities\Facades\IdentityCrypt;
use SocialiteProviders\GitLab\Provider;
use SocialiteProviders\Manager\OAuth2\User as OauthUser;
use Tests\Fixtures\Concern\UseTestFixtures;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase, UseTestFixtures;

    public function test_redirect_to_provider()
    {
        IdentityFacade::useNamespace('Tests\\Fixtures');
        IdentityFacade::routes();

        $response = $this->get(route('oneofftech::login.provider', ['provider' => 'gitlab']));

        $response->assertRedirect();

        $location = urldecode($response->headers->get('Location'));

        $this->assertStringContainsString('gitlab.com', $location);
        $this->assertStringContainsString(route('oneofftech::login.callback', ['provider' => 'gitlab']), $location);
    }

    public function test_user_login()
    {
        $user = $this->createUser();

        $identity = $user->identities()->create([
            'provider' => 'gitlab',
            'provider_id' => IdentityCrypt::hash('U1'),
            'token' => 'T1',
            'refresh_token' => null,
            'expires_at' => null,
            'registration' => true,
        ]);

        $this->withoutExceptionHandling();

        $driverMock = Mockery::mock(Provider::class)->makePartial();

        $oauthFakeUser = (new OauthUser)->map([
            'id' => 'U1',
            'nickname' => 'User',
            'name' => 'User',
            'email' => 'user@local.com',
            'avatar' => 'https://gitlab.com',
            'token' => 'T1',
        ]);

        $driverMock->shouldReceive('user')->andReturn($oauthFakeUser);

        $driverMock->shouldReceive('redirectUrl')->andReturn($driverMock);

        IdentityFacade::shouldReceive('driver')->with('gitlab')->andReturn($driverMock);

        $response = $this->get(route('oneofftech::login.callback', ['provider' => 'gitlab']));

        $response->assertRedirect('http://localhost/home');

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_login_denied_if_identity_cannot_be_found()
    {
        $user = $this->createUser();

        $identity = $user->identities()->create([
            'provider' => 'facebook',
            'provider_id' => IdentityCrypt::hash('U2'),
            'token' => 'T1',
            'refresh_token' => null,
            'expires_at' => null,
            'registration' => true,
        ]);

        $this->withoutExceptionHandling();

        $driverMock = Mockery::mock(Provider::class)->makePartial();

        $oauthFakeUser = (new OauthUser)->map([
            'id' => 'U1',
            'nickname' => 'User',
            'name' => 'User',
            'email' => 'user@local.com',
            'avatar' => 'https://gitlab.com',
            'token' => 'T1',
        ]);

        $driverMock->shouldReceive('user')->andReturn($oauthFakeUser);

        $driverMock->shouldReceive('redirectUrl')->andReturn($driverMock);

        IdentityFacade::shouldReceive('driver')->with('gitlab')->andReturn($driverMock);

        $this->expectException(ValidationException::class);

        $response = $this->get(route('oneofftech::login.callback', ['provider' => 'gitlab']));

        $this->assertGuest();
    }
}

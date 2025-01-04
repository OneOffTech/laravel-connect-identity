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
use Tests\Fixtures\User;
use Tests\TestCase;

class RegistrationControllerTest extends TestCase
{
    use RefreshDatabase, UseTestFixtures;

    public function test_redirect_to_provider()
    {
        $response = $this->get(route('oneofftech::register.provider', ['provider' => 'gitlab']));

        $response->assertRedirect();

        $location = urldecode($response->headers->get('Location'));

        $this->assertStringContainsString('gitlab.com', $location);
        $this->assertStringContainsString(route('oneofftech::register.callback', ['provider' => 'gitlab']), $location);
    }

    public function test_user_can_be_registered()
    {
        IdentityFacade::useIdentityModel('Tests\\Fixtures\\Identity');

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

        $response = $this->get(route('oneofftech::register.callback', ['provider' => 'gitlab']));

        $response->assertRedirect('http://localhost/home');

        $user = User::first();

        $this->assertNotNull($user);
        $this->assertEquals('User', $user->name);
        $this->assertEquals('user@local.com', $user->email);

        $linkedIdentities = $user->identities;

        $this->assertNotNull($linkedIdentities);
        $this->assertEquals(1, $linkedIdentities->count());

        $firstIdentity = $linkedIdentities->first();

        $this->assertEquals(IdentityCrypt::hash('U1'), $firstIdentity->provider_id);
        $this->assertEquals('gitlab', $firstIdentity->provider);
        $this->assertNotNull($firstIdentity->token);
        $this->assertTrue($firstIdentity->registration);
    }

    public function test_user_cannot_register_twice()
    {
        $this->createUser();

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

        $this->get(route('oneofftech::register.callback', ['provider' => 'gitlab']));
    }
}

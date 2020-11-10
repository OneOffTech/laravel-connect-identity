<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SocialiteProviders\GitLab\Provider;
use Mockery;
use Oneofftech\Identities\Facades\Identity as IdentityFacade;
use Oneofftech\Identities\Facades\IdentityCrypt;
use SocialiteProviders\Manager\OAuth2\User as OauthUser;
use Tests\Fixtures\User;

class RegistrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_to_provider()
    {
        IdentityFacade::useNamespace('Tests\\Fixtures');
        IdentityFacade::routes();

        $response = $this->get(route('oneofftech::register.provider', ['provider' => 'gitlab']));

        $response->assertRedirect();
        
        $location = urldecode($response->headers->get('Location'));

        $this->assertStringContainsString('gitlab.com', $location);
        $this->assertStringContainsString(route('oneofftech::register.callback', ['provider' => 'gitlab']), $location);
    }

    public function test_callback()
    {
        IdentityFacade::useIdentityModel('Tests\\Fixtures\\Identity');
        IdentityFacade::useNamespace('Tests\\Fixtures');
        IdentityFacade::routes();

        $this->withoutExceptionHandling();

        $driverMock = Mockery::mock(Provider::class)->makePartial();

        $oauthFakeUser = (new OauthUser())->map([
            'id'       => 'U1',
            'nickname' => 'User',
            'name'     => 'User',
            'email'    => 'user@local.com',
            'avatar'   => 'https://gitlab.com',
            'token'   => 'T1',
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
    }
}

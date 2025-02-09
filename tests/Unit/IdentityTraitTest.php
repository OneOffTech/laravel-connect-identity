<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Oneofftech\Identities\Facades\Identity as IdentityFacade;
use Oneofftech\Identities\Facades\IdentityCrypt;
use Oneofftech\Identities\Support\FindIdentity;
use Orchestra\Testbench\Attributes\WithMigration;
use Tests\Fixtures\Identity;
use Tests\Fixtures\User;
use Tests\TestCase;

#[WithMigration]
class IdentityTraitTest extends TestCase
{
    use FindIdentity, RefreshDatabase;

    public function test_with_identity_trait()
    {
        IdentityFacade::useNamespace('Tests\\Fixtures\\');
        IdentityFacade::useIdentityModel('Tests\\Fixtures\\Identity');
        IdentityFacade::useUserModel('Tests\\Fixtures\\User');

        $user = tap((new User), function ($u) {
            $u->forceFill([
                'email' => 'user@local.local',
                'name' => 'User',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'remember_token' => Str::random(10),
            ])->save();
        });

        $identity = $user->identities()->create([
            'provider' => 'social',
            'provider_id' => 'aaaa',
            'token' => 'tttt',
            'refresh_token' => null,
            'expires_at' => null,
            'registration' => true,
        ]);

        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertNotNull($identity->getKey());
        $this->assertEquals('social', $identity->provider);
        $this->assertEquals('aaaa', $identity->provider_id);
        $this->assertEquals('tttt', $identity->token);
        $this->assertNull($identity->refresh_token);
        $this->assertNull($identity->expires_at);
        $this->assertTrue($identity->registration);
        $this->assertTrue($identity->user->is($user));
    }

    public function test_find_identity_trait()
    {
        IdentityFacade::useNamespace('Tests\\Fixtures\\');
        IdentityFacade::useIdentityModel('Tests\\Fixtures\\Identity');
        IdentityFacade::useUserModel('Tests\\Fixtures\\User');

        $expectedUser = tap((new User), function ($u) {
            $u->forceFill([
                'email' => 'user@local.local',
                'name' => 'User',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'remember_token' => Str::random(10),
            ])->save();

            $u->identities()->create([
                'provider' => 'social',
                'provider_id' => IdentityCrypt::hash('P1'),
                'token' => 'tttt',
                'refresh_token' => null,
                'expires_at' => null,
                'registration' => true,
            ]);
        });

        $user = $this->findUserFromIdentity(new class implements SocialiteUser
        {
            public function getId()
            {
                return 'P1';
            }

            public function getNickname()
            {
                return null;
            }

            public function getName()
            {
                return null;
            }

            public function getEmail()
            {
                return null;
            }

            public function getAvatar()
            {
                return null;
            }
        }, 'social');

        $this->assertTrue($expectedUser->is($user));
    }

    public function test_find_identity_trait_return_null_when_not_found()
    {
        IdentityFacade::useNamespace('Tests\\Fixtures\\');
        IdentityFacade::useIdentityModel('Tests\\Fixtures\\Identity');
        IdentityFacade::useUserModel('Tests\\Fixtures\\User');

        $user = $this->findUserFromIdentity(new class implements SocialiteUser
        {
            public function getId()
            {
                return 'P1';
            }

            public function getNickname()
            {
                return null;
            }

            public function getName()
            {
                return null;
            }

            public function getEmail()
            {
                return null;
            }

            public function getAvatar()
            {
                return null;
            }
        }, 'social');

        $this->assertNull($user);
    }

    public function test_find_user()
    {
        IdentityFacade::useNamespace('Tests\\Fixtures\\');
        IdentityFacade::useIdentityModel('Tests\\Fixtures\\Identity');
        IdentityFacade::useUserModel('Tests\\Fixtures\\User');

        $expectedUser = tap((new User), function ($u) {
            $u->forceFill([
                'email' => 'user@local.local',
                'name' => 'User',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'remember_token' => Str::random(10),
            ])->save();
        });

        $user = IdentityFacade::findUserByIdOrFail($expectedUser->getKey());

        $this->assertTrue($expectedUser->is($user));
    }
}

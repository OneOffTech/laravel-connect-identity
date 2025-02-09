<?php

namespace Tests\Unit;

use Oneofftech\Identities\Facades\Identity as IdentityFacade;
use Tests\Fixtures\Identity;
use Tests\Fixtures\User;
use Tests\TestCase;

class IdentityModelsConfigurationTest extends TestCase
{
    public function test_default_models_uses_app_namespace()
    {
        $this->assertEquals('App\\User', IdentityFacade::userModel());
        $this->assertEquals('App\\Identity', IdentityFacade::identityModel());
    }

    public function test_user_model_can_be_customized()
    {
        IdentityFacade::useUserModel(User::class);

        $this->assertEquals(User::class, IdentityFacade::userModel());

        $this->assertInstanceOf(User::class, IdentityFacade::newUserModel());
    }

    public function test_identity_model_can_be_customized()
    {
        IdentityFacade::useIdentityModel(Identity::class);

        $this->assertEquals(Identity::class, IdentityFacade::identityModel());

        $this->assertInstanceOf(Identity::class, IdentityFacade::newIdentityModel());
    }
}

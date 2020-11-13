<?php

namespace Tests\Fixtures\Concern;

use Oneofftech\Identities\Facades\Identity as IdentityFacade;

trait UseTestFixtures
{
    protected function useTestFixtures()
    {
        IdentityFacade::useUserModel("Tests\\Fixtures\\User");
        IdentityFacade::useIdentityModel('Tests\\Fixtures\\Identity');
        IdentityFacade::useNamespace('Tests\\Fixtures');
        IdentityFacade::routes();
    }
}

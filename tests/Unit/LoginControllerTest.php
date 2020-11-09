<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Oneofftech\Identities\Facades\Identity as IdentityFacade;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

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
}

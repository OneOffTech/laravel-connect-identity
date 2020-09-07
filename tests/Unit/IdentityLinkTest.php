<?php

namespace Tests\Unit;

use Tests\TestCase;
use Oneofftech\Identities\Facades\Identity;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use InvalidArgumentException;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use Oneofftech\Identities\View\Components\IdentityLink;

class IdentityLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        Identity::routes();
    }

    public function test_unsupported_action_throws()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Specified action [dance] is not supported.");

        new IdentityLink('gitlab', 'dance');
    }

    public function test_component_uses_default_label()
    {
        $component = new IdentityLink('gitlab', 'register');

        $this->assertEquals('gitlab', $component->provider);
        $this->assertEquals('register', $component->action);
        $this->assertEquals('Register via :Provider', $component->label);
        $this->assertEquals([], $component->parameters);
    }

    public function test_component_uses_custom_label()
    {
        $component = new IdentityLink('gitlab', 'register', 'My label');

        $this->assertEquals('gitlab', $component->provider);
        $this->assertEquals('register', $component->action);
        $this->assertEquals('My label', $component->label);
        $this->assertEquals([], $component->parameters);
    }

    public function test_login_link_rendered()
    {
        $component = new IdentityLink('gitlab');

        $view = $this->render($component);

        $this->assertStringContainsString('http://localhost/login-via/gitlab', $view);
        $this->assertStringContainsString('Log in via Gitlab', $view);
    }

    public function test_register_link_rendered()
    {
        $component = new IdentityLink('gitlab', 'register', 'My label');

        $view = $this->render($component);

        $this->assertStringContainsString('http://localhost/register-via/gitlab', $view);
        $this->assertStringContainsString('My label', $view);
    }

    private function render(Component $component)
    {
        return view($component->resolveView(), $component->data())->render();
    }
}

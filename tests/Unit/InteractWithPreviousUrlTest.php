<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Session;
use Oneofftech\Identities\Support\InteractsWithPreviousUrl;

class InteractWithPreviousUrlTest extends TestCase
{
    use InteractsWithPreviousUrl;

    public function test_previous_url_is_stored()
    {
        Session::shouldReceive('previousUrl')->andReturn('http://localhost/previous');

        Session::shouldReceive('put')->once()->with('_oot.identities.previous_url', 'http://localhost/previous');

        $this->savePreviousUrl();
    }
    
    public function test_previous_url_can_be_retrieved()
    {
        Session::shouldReceive('previousUrl')->andReturnNull();

        Session::shouldReceive('pull')->once()->with('_oot.identities.previous_url', 'http://localhost')->andReturn('http://localhost/previous');

        $url = $this->getPreviousUrl();

        $this->assertEquals('http://localhost/previous', $url);
    }
}

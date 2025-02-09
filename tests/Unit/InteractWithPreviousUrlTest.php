<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Session;
use Oneofftech\Identities\Support\InteractsWithPreviousUrl;
use Tests\TestCase;

class InteractWithPreviousUrlTest extends TestCase
{
    use InteractsWithPreviousUrl;

    public function test_previous_url_is_stored()
    {
        Session::put('_previous.url', 'http://localhost/previous');

        $this->savePreviousUrl();

        $this->assertEquals('http://localhost/previous', Session::get('_oot.identities.previous_url'));
    }

    public function test_previous_url_can_be_retrieved()
    {
        Session::put('_previous.url', null);

        Session::put('_oot.identities.previous_url', 'http://localhost/previous');
        
        $url = $this->getPreviousUrl();

        $this->assertEquals('http://localhost/previous', $url);
    }
}

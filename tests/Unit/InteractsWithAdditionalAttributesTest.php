<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Oneofftech\Identities\Support\InteractsWithAdditionalAttributes;
use Tests\TestCase;

class InteractsWithAdditionalAttributesTest extends TestCase
{
    use InteractsWithAdditionalAttributes;

    public function test_attributes_not_defined()
    {
        $attributes = $this->redirectAttributes();

        $this->assertEquals([], $attributes);
    }

    public function test_nothing_is_saved()
    {
        $request = Request::create('http://localhost', 'GET', [
            'attribute' => 'value',
        ]);

        $request->setLaravelSession(Session::driver());

        $this->pushAttributes($request);

        $this->assertTrue(Session::missing('_oot.identities.attributes'));
    }

    public function test_nothing_is_retrieved()
    {
        $request = Request::create('http://localhost/callback');

        $request->setLaravelSession(Session::driver());

        $data = $this->pullAttributes($request);

        $this->assertEquals([], $data);

        $this->assertTrue(Session::missing('_oot.identities.attributes'));
    }
}

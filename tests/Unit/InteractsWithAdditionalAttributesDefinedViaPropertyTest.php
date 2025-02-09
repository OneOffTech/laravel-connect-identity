<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Oneofftech\Identities\Support\InteractsWithAdditionalAttributes;
use Tests\TestCase;

class InteractsWithAdditionalAttributesDefinedViaPropertyTest extends TestCase
{
    use InteractsWithAdditionalAttributes;

    protected $attributes = ['attribute'];

    public function test_attributes_can_be_defined()
    {
        $attributes = $this->redirectAttributes();

        $this->assertEquals(['attribute'], $attributes);
    }

    public function test_attributes_are_saved()
    {
        $request = Request::create('http://localhost', 'GET', [
            'attribute' => 'value',
        ]);
        $request->setLaravelSession(Session::driver());

        $this->pushAttributes($request);

        $this->assertEquals('{"attribute":"value"}', Session::get('_oot.identities.attributes'));
        $this->assertNull(Session::previousUrl());
    }

    public function test_attributes_are_retrieved()
    {
        Session::put('_oot.identities.attributes', '{"attribute":"http://localhost/previous"}');

        $request = Request::create('http://localhost/callback');
        $request->setLaravelSession(Session::driver());

        $data = $this->pullAttributes($request);

        $this->assertEquals(['attribute' => 'http://localhost/previous'], $data);
        $this->assertNull(Session::previousUrl());
    }
}

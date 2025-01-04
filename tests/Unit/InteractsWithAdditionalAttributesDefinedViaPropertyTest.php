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
        Session::shouldReceive('put')->once()->with('_oot.identities.attributes', '{"attribute":"value"}');

        $request = Request::create('http://localhost', 'GET', [
            'attribute' => 'value',
        ]);
        $request->setLaravelSession(Session::getFacadeRoot());

        $this->pushAttributes($request);
    }

    public function test_attributes_are_retrieved()
    {
        Session::shouldReceive('previousUrl')->andReturnNull();

        Session::shouldReceive('pull')->once()
            ->with('_oot.identities.attributes')
            ->andReturn('{"attribute":"http://localhost/previous"}');

        $request = Request::create('http://localhost/callback');
        $request->setLaravelSession(Session::getFacadeRoot());

        $data = $this->pullAttributes($request);

        $this->assertEquals(['attribute' => 'http://localhost/previous'], $data);
    }
}

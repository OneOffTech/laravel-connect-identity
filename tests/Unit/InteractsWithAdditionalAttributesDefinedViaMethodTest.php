<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;
use Oneofftech\Identities\Support\InteractsWithAdditionalAttributes;

class InteractsWithAdditionalAttributesDefinedViaMethodTest extends TestCase
{
    use InteractsWithAdditionalAttributes;

    protected function attributes()
    {
        return ['attribute'];
    }

    public function test_attributes_can_be_defined()
    {
        $attributes = $this->redirectAttributes();

        $this->assertEquals(['attribute'], $attributes);
    }
    
    public function test_attributes_are_saved()
    {
        Session::shouldReceive('put')->once()->with('_oot.identities.attributes', '{"attribute":"value"}');

        $request = Request::create('http://localhost', 'GET', [
            'attribute' => 'value'
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

    public function test_attributes_not_retrieved_if_nothing_saved()
    {
        Session::shouldReceive('previousUrl')->andReturnNull();

        Session::shouldReceive('pull')->once()
            ->with('_oot.identities.attributes')
            ->andReturn(null);

        $request = Request::create('http://localhost/callback');
        $request->setLaravelSession(Session::getFacadeRoot());

        $data = $this->pullAttributes($request);

        $this->assertEquals([], $data);
    }
}

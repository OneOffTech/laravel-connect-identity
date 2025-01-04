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
        Session::shouldReceive('put')->never();

        $request = Request::create('http://localhost', 'GET', [
            'attribute' => 'value',
        ]);
        $request->setLaravelSession(Session::getFacadeRoot());

        $this->pushAttributes($request);
    }

    public function test_nothing_is_retrieved()
    {
        Session::shouldReceive('previousUrl')->andReturnNull();

        Session::shouldReceive('pull')->never();

        $request = Request::create('http://localhost/callback');
        $request->setLaravelSession(Session::getFacadeRoot());

        $data = $this->pullAttributes($request);

        $this->assertEquals([], $data);
    }
}

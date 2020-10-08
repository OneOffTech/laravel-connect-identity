<?php

namespace Tests\Fixtures\Http\Controllers\Identities\Auth;

use Tests\Fixtures\User;
use Illuminate\Support\Facades\Validator;
use Tests\Fixtures\Http\Controllers\Controller;
use Oneofftech\Identities\Auth\ConnectUserIdentity;

class ConnectController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Connect Identity Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the connection (or update) of an
    | identity for an already authenticated user.
    | The controller uses a trait to conveniently provide its
    | functionality to your applications.
    |
    */

    use ConnectUserIdentity;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get a validator for an incoming connection request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}

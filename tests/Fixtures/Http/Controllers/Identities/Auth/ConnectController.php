<?php

namespace Tests\Fixtures\Http\Controllers\Identities\Auth;

use Illuminate\Support\Facades\Validator;
use Oneofftech\Identities\Auth\ConnectUserIdentity;
use Tests\Fixtures\Http\Controllers\Controller;
use Tests\Fixtures\User;

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
     * Get a validator for an incoming connection request.
     *
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

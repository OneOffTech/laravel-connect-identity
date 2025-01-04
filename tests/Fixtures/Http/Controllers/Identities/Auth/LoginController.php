<?php

namespace Tests\Fixtures\Http\Controllers\Identities\Auth;

use Oneofftech\Identities\Auth\AuthenticatesUsersWithIdentity;
use Tests\Fixtures\Http\Controllers\Controller;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login via Identity Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users via their connected
    | identities provided by third party authentication services.
    | The controller uses a trait to conveniently provide its
    | functionality to your applications.
    |
    */

    use AuthenticatesUsersWithIdentity;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
}

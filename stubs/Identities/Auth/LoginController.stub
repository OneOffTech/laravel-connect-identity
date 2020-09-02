<?php

namespace App\Http\Controllers\Identities\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Oneofftech\Identities\Auth\AuthenticatesUsersWithIdentity;

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
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

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

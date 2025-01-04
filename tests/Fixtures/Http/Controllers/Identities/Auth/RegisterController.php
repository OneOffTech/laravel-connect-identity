<?php

namespace Tests\Fixtures\Http\Controllers\Identities\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Oneofftech\Identities\Auth\RegistersUsersWithIdentity;
use Tests\Fixtures\Http\Controllers\Controller;
use Tests\Fixtures\User;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register via Identity Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users via identities
    | provided by third party authentication services. The controller
    | uses a trait to conveniently provide its functionality.
    |
    */

    use RegistersUsersWithIdentity;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
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

    /**
     * Create a new user instance after a valid registration.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|\App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? Str::random(20)),
        ]);
    }
}

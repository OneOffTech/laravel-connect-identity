<?php

namespace Oneofftech\Identities\Auth;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Validation\ValidationException;
use Oneofftech\Identities\Facades\Identity;
use Oneofftech\Identities\Support\InteractsWithPreviousUrl;

trait AuthenticatesUsersWithIdentity
{
    use RedirectsUsers, InteractsWithPreviousUrl;

    
    /**
     * Redirect the user to the provider authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirect($provider)
    {
        // save the previous url as the callback will
        // probably have the referrer header set 
        // and in case of validation errors the
        // referrer has precedence over _previous.url
        // $request->session()->put('_oot.identities.previous_url', url()->previous());
        $this->savePreviousUrl();

        return Identity::driver($provider)
            ->redirectUrl(route('oneofftech::login.callback', ['provider' => $provider]))
            ->redirect();
    }

    /**
     * Log in the user using the information coming 
     * from the authentication provider.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request, $provider)
    {
        // Load the previous url from the
        // session to redirect back in
        // case of errors
        $previous_url = $this->getPreviousUrl();

        $identity = Identity::driver($provider)
            ->redirectUrl(route('oneofftech::login.callback', ['provider' => $provider]))
            ->user();

        // if user denies the authorization request we get
        // GuzzleHttp\Exception\ClientException
        // Client error: `POST https://gitlab.com/oauth/token` resulted in a `401 Unauthorized` response: {"error":"invalid_grant","error_description":"The provided authorization grant is invalid, expired, revoked, does not ma (truncated...)
        // $this->sendFailedLoginResponse
        

        // If we find a registered user with the 
        // same identity we attempt to login

        $user = $this->findUserFromIdentity($identity, $provider);

        if(! $user){
            $this->sendFailedLoginResponse($request, $provider);
        }
        
        $this->guard()->login($user /*, $remember = false*/);
            
        return $this->sendLoginResponse($request);
        
        // $this->validateLogin($user);

        // // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // // the login attempts for this application. We'll key this by the username and
        // // the IP address of the client making these requests into this application.
        // if (method_exists($this, 'hasTooManyLoginAttempts') &&
        //     $this->hasTooManyLoginAttempts($request)) {
        //     $this->fireLockoutEvent($request);

        //     return $this->sendLockoutResponse($request);
        // }

        // if ($this->attemptLogin($request)) {
        //     return $this->sendLoginResponse($request);
        // }

        // // If the login attempt was unsuccessful we will increment the number of attempts
        // // to login and redirect the user back to the login form. Of course, when this
        // // user surpasses their maximum number of attempts they will get locked out.
        // $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request, $provider);
    }


    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected function findUserFromIdentity($identity, $provider)
    {
        try{

            return Identity::findUserByIdentity($provider, $identity->getId());

        } catch(ModelNotFoundException $mntfex){
            return null;
        }
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request, $provider)
    {
        throw ValidationException::withMessages([
            "$provider" => [trans('auth.failed')],
        ])->redirectTo($this->getPreviousUrl());
    }

    /**
     * Register or login a user by connecting
     * the identity provider
     * 
     * 
     */
    // protected function connect($provider, SocialiteUser $user)
    // {        
    //     /**
    //      * @var App\User
    //      */
    //     $localUser = Auth::user();

    //     // if not already logged in and the provider do not share the email
    //     // address then we don't have a way to identify the user so abort
    //     abort_unless($user->getEmail(), '422', 'Could not get email address');

    //     $registeredUser = DB::transaction(function() use ($localUser, $user, $provider){
    
    //         if(!$localUser){
    //             // if user not logged-in then register the new user
    //             $localUser = User::findFromIdentityOrCreate($user->getEmail(), $provider, $user->getId(), [
    //                 'name' => $user->getName() ?? $user->getNickname(),
    //                 'email_verified_at' => now(),
    //             ]);
    //         }
    
    //         $localUser->identities()->updateOrCreate([
    //                 'provider'=> $provider, 
    //                 'provider_id'=> $user->getId()
    //             ],
    //             [
    //                 'token'=> $user->token,
    //                 'refresh_token'=> $user->refreshToken,
    //                 'expires_at'=> $user->expiresIn ? now()->addSeconds($user->expiresIn) : null
    //             ]);

    //         return $localUser;
    //     });

    //     return $registeredUser;
    // }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        // $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($this->guard()->user())) {
            return $response;
        }

        return redirect()->intended($this->redirectPath());
    }


    /**
     * The user has been authenticated.
     *
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated($user)
    {
        //
    }
    
    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}

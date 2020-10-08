<?php

namespace Oneofftech\Identities\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Validation\ValidationException;
use Oneofftech\Identities\Facades\Identity;
use Oneofftech\Identities\Support\FindIdentity;
use Oneofftech\Identities\Support\InteractsWithPreviousUrl;

trait AuthenticatesUsersWithIdentity
{
    use RedirectsUsers, InteractsWithPreviousUrl, FindIdentity;

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

        if (! $user) {
            $this->sendFailedLoginResponse($request, $provider);
        }
        
        $this->guard()->login($user /*, $remember = false*/);
            
        return $this->sendLoginResponse($request);
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
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        if ($response = $this->authenticated($request, $this->guard()->user())) {
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
    protected function authenticated(Request $request, $user)
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

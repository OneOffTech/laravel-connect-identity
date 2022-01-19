<?php

namespace Oneofftech\Identities\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Oneofftech\Identities\Facades\IdentityCrypt;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\AbstractUser as SocialiteUser;
use Oneofftech\Identities\Facades\Identity;
use Oneofftech\Identities\Support\InteractsWithPreviousUrl;
use Oneofftech\Identities\Support\InteractsWithAdditionalAttributes;

trait RegistersUsersWithIdentity
{
    use RedirectsUsers, InteractsWithPreviousUrl, InteractsWithAdditionalAttributes;

    /**
     * Redirect the user to the Authentication provider authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirect(Request $request, $provider)
    {
        // save the previous url as the callback will
        // probably have the referrer header set
        // and in case of validation errors the
        // referrer has precedence over _previous.url
        $this->savePreviousUrl();

        // get additional user defined attributes
        $this->pushAttributes($request);

        return Identity::driver($provider)
            ->redirectUrl(route('oneofftech::register.callback', ['provider' => $provider]))
            ->redirect();
    }

    /**
     * Obtain the user information from Authentication provider.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request, $provider)
    {
        // Load the previous url from the
        // session to redirect back in
        // case of errors
        $previous_url = $this->getPreviousUrl();

        $oauthUser = Identity::driver($provider)
            ->redirectUrl(route('oneofftech::register.callback', ['provider' => $provider]))
            ->user();

        // if user denies the authorization request we get
        // GuzzleHttp\Exception\ClientException
        // Client error: `POST https://gitlab.com/oauth/token` resulted in a `401 Unauthorized`
        // response: {"error":"invalid_grant","error_description":"The provided authorization grant is invalid, expired, revoked, does not ma (truncated...)

        // GuzzleHttp\Exception\ClientException
        // Client error: `POST https://gitlab/oauth/token` resulted in a `401 Unauthorized`
        // response: {"error":"invalid_grant","error_description":"The provided authorization grant is invalid, expired, revoked, does not ma (truncated...)

        /**
         * @var \Illuminate\Contracts\Validation\Validator
         */
        $validator = $this->validator($this->map($request, $oauthUser));

        if ($validator->fails()) {

            // throw a validation error that uses
            // $provider as the key

            throw ValidationException::withMessages([
                "$provider" => Arr::flatten($validator->errors()->all()),
            ])->redirectTo($previous_url);
        }

        $data = $validator->validated();

        // create user and associate the identity

        $user = DB::transaction(function () use ($data, $provider, $oauthUser) {
            $user = $this->create($data);
    
            $this->createIdentity($user, $provider, $oauthUser);

            return $user;
        });

        event(new Registered($user));
        
        $this->guard()->login($user /*, $remember = false*/);
            
        return $this->sendRegistrationResponse($request, $provider);
    }

    /**
     * Maps the socialite user to attributes
     *
     * @param SocialiteUser $oauthUser
     * @return array
     */
    protected function map(Request $request, SocialiteUser $oauthUser)
    {
        $user = [
            'name' => $oauthUser->getName() ?? $oauthUser->getNickname(),
            'email' => $oauthUser->getEmail(),
            'avatar' => $oauthUser->getAvatar(),
        ];

        return array_merge($user, $this->pullAttributes($request));
    }

    protected function createIdentity($user, $provider, $oauthUser)
    {
        return $user->identities()->updateOrCreate(
            [
                'provider'=> $provider,
                'provider_id'=> IdentityCrypt::hash($oauthUser->getId())
            ],
            [
                'token'=> IdentityCrypt::encryptString($oauthUser->token),
                'refresh_token'=> IdentityCrypt::encryptString($oauthUser->refreshToken),
                'expires_at'=> $oauthUser->expiresIn ? now()->addSeconds($oauthUser->expiresIn) : null,
                'registration' => true,
            ]
        );
    }

    protected function sendRegistrationResponse(Request $request, $provider)
    {
        $request->session()->regenerate();

        if ($response = $this->registered($request, $this->guard()->user(), $provider)) {
            return $response;
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been registered.
     *
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user, $provider)
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

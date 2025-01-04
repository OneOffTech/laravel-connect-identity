<?php

namespace Oneofftech\Identities\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Oneofftech\Identities\Facades\Identity;
use Oneofftech\Identities\Facades\IdentityCrypt;
use Oneofftech\Identities\Support\FindIdentity;
use Oneofftech\Identities\Support\InteractsWithAdditionalAttributes;
use Oneofftech\Identities\Support\InteractsWithPreviousUrl;

trait ConnectUserIdentity
{
    use FindIdentity, InteractsWithAdditionalAttributes, InteractsWithPreviousUrl, RedirectsUsers;

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
            ->redirectUrl(route('oneofftech::connect.callback', ['provider' => $provider]))
            ->redirect();
    }

    /**
     * Obtain the user information from Authentication provider.
     *
     * if the identity exists it will be updated, otherwise a new identity will be created
     *
     * @return \Illuminate\Http\Response
     */
    public function connect(Request $request, $provider)
    {
        // Load the previous url from the
        // session to redirect back in
        // case of errors
        $previous_url = $this->getPreviousUrl();

        $oauthUser = Identity::driver($provider)
            ->redirectUrl(route('oneofftech::connect.callback', ['provider' => $provider]))
            ->user();

        // if user denies the authorization request we get
        // GuzzleHttp\Exception\ClientException
        // Client error: `POST https://gitlab.com/oauth/token` resulted in a `401 Unauthorized`
        // response: {"error":"invalid_grant","error_description":"The provided authorization grant is invalid, expired, revoked, does not ma (truncated...)

        // GuzzleHttp\Exception\ClientException
        // Client error: `POST https://gitlab/oauth/token` resulted in a `401 Unauthorized`
        // response: {"error":"invalid_grant","error_description":"The provided authorization grant is invalid, expired, revoked, does not ma (truncated...)

        $user = $request->user();

        // create or update the user's identity

        [$user, $identity] = DB::transaction(function () use ($user, $provider, $oauthUser) {
            $identity = $this->createIdentity($user, $provider, $oauthUser);

            return [$user, $identity];
        });

        // todo: event(new Connected($user, $identity));

        return $this->sendConnectionResponse($request, $identity);
    }

    protected function createIdentity($user, $provider, $oauthUser)
    {
        return $user->identities()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_id' => IdentityCrypt::hash($oauthUser->getId()),
            ],
            [
                'token' => IdentityCrypt::encryptString($oauthUser->token),
                'refresh_token' => IdentityCrypt::encryptString($oauthUser->refreshToken),
                'expires_at' => $oauthUser->expiresIn ? now()->addSeconds($oauthUser->expiresIn) : null,
                'registration' => false,
            ]
        );
    }

    protected function sendConnectionResponse(Request $request, $identity)
    {
        $request->session()->regenerate();

        if ($response = $this->connected($this->guard()->user(), $identity, $this->pullAttributes($request), $request)) {
            return $response;
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * The user identity has been connected.
     *
     * @param  mixed  $user
     * @param  mixed  $identity
     * @return mixed
     */
    protected function connected($user, $identity, array $attributes, Request $request)
    {
        //
    }

    /**
     * Get the guard to retrieve currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}

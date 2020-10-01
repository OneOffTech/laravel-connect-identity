<?php

namespace Oneofftech\Identities\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Support\Facades\DB;
use Oneofftech\Identities\Facades\IdentityCrypt;
use Oneofftech\Identities\Facades\Identity;
use Oneofftech\Identities\Support\InteractsWithPreviousUrl;
use Oneofftech\Identities\Support\FindIdentity;

trait ConnectUserIdentity
{
    use RedirectsUsers, InteractsWithPreviousUrl, FindIdentity;

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

        list($user, $identity) = DB::transaction(function () use ($user, $provider, $oauthUser) {
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

    protected function sendConnectionResponse(Request $request, $identity)
    {
        $request->session()->regenerate();

        if ($response = $this->connected($request, $this->guard()->user(), $identity)) {
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
    protected function connected(Request $request, $user, $identity)
    {
        //
    }

    /**
     * The attributes that should be retrieved from
     * the request to append to the redirect
     *
     * @var array
     */
    protected function redirectAttributes()
    {
        if (method_exists($this, 'attributes')) {
            return $this->attributes();
        }

        return property_exists($this, 'attributes') ? $this->attributes : [];
    }

    protected function pushAttributes($request)
    {
        $attributes = $this->redirectAttributes() ?? [];

        if (empty($attributes)) {
            return;
        }
        
        $request->session()->put('_oot.identities.attributes', json_encode($request->only($attributes)));
    }

    protected function pullAttributes($request)
    {
        $attributes = $this->redirectAttributes() ?? [];

        if (empty($attributes)) {
            return [];
        }

        $savedAttributes = $request->session()->pull('_oot.identities.attributes') ?? null;

        if (! $savedAttributes) {
            return [];
        }

        return json_decode($savedAttributes, true);
    }
}

<?php

namespace Oneofftech\Identities\Support;

use Oneofftech\Identities\Facades\Identity;
use Oneofftech\Identities\Facades\IdentityCrypt;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait FindIdentity
{
    /**
     * Search a user given its identity in the third party provider
     *
     * @param \Laravel\Socialite\Contracts\User $identity Third party identity provided by Laravel Socialite
     * @param string $provider The identity provider name
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected function findUserFromIdentity($identity, $provider)
    {
        try {
            return Identity::findUserByIdentity($provider, IdentityCrypt::hash($identity->getId()));
        } catch (ModelNotFoundException $mntfex) {
            return null;
        }
    }
}

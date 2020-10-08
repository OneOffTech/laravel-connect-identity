<?php

namespace Oneofftech\Identities\Support;

use Oneofftech\Identities\Facades\Identity;
use Oneofftech\Identities\Facades\IdentityCrypt;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait FindIdentity
{
    /**
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

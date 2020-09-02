<?php

namespace Oneofftech\Identities;

use Oneofftech\Identities\Facades\Identity;

trait WithIdentities
{
    
    /**
     * Get the associated identities
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|mixed
     */
    public function identities()
    {
        return $this->hasMany(Identity::identityModel());
    }
}

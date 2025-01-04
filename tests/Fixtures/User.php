<?php

namespace Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Oneofftech\Identities\WithIdentities;

class User extends Authenticatable
{
    use WithIdentities;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}

<?php

namespace Tests\Fixtures;

use Oneofftech\Identities\WithIdentities;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use WithIdentities;
}

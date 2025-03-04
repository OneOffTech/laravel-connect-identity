<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Oneofftech\Identities\Facades\Identity as IdentityFacade;

class Identity extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'provider_id',
        'provider',
        'token',
        'refresh_token',
        'expires_at',
        'registration',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'provider_id',
        'token',
        'refresh_token',
        'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'registration' => 'bool',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(IdentityFacade::userModel());
    }
}

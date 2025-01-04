<?php

namespace Oneofftech\Identities\Encryption;

use Illuminate\Encryption\Encrypter as BaseEncrypter;
use Illuminate\Support\Str;

class Encrypter extends BaseEncrypter
{
    /**
     * Decrypt the given value.
     *
     * @param  string  $payload
     * @param  bool  $unserialize
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Encryption\DecryptException
     */
    public function decrypt($payload, $unserialize = true)
    {
        try {
            return parent::decrypt($payload, $unserialize);
        } catch (\Throwable $e) {
            $currentKey = $this->key;

            $this->key = Str::startsWith(config('identities.old_key'), 'base64:')
                            ? base64_decode(substr(config('identities.old_key'), 7))
                            : config('identities.old_key');

            return tap(parent::decrypt($payload, $unserialize), function () use ($currentKey) {
                $this->key = $currentKey;
            });
        }
    }
}

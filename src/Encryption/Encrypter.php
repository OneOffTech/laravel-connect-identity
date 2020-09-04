<?php

namespace Oneofftech\Identities\Encryption;

use Illuminate\Support\Str;
use Illuminate\Encryption\Encrypter as BaseEncrypter;

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

            $this->key = Str::startsWith(config('identity.old_key'), 'base64:')
                            ? base64_decode(substr(config('identity.old_key'), 7))
                            : config('identity.old_key');

            return tap(parent::decrypt($payload, $unserialize), function () use ($currentKey) {
                $this->key = $currentKey;
            });
        }
    }
}

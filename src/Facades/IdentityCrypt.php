<?php

namespace Oneofftech\Identities\Facades;

use Illuminate\Support\Facades\Facade;
use Oneofftech\Identities\Encryption\Encrypter;

/**
 * @method static bool supported(string $key, string $cipher)
 * @method static mixed decrypt(string $payload, bool $unserialize = true)
 * @method static string decryptString(string $payload)
 * @method static string encrypt(mixed $value, bool $serialize = true)
 * @method static string encryptString(string $value)
 * @method static string generateKey(string $cipher)
 * @method static string getKey()
 *
 * @see \Illuminate\Encryption\Encrypter
 * @see \Oneofftech\Identities\Encryption\Encrypter
 */
class IdentityCrypt extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Encrypter::class;
    }

    /**
     * Hash a string for pseudoanonymization
     *
     * @param string $value
     * @return string
     */
    public static function hash($value)
    {
        return hash('sha512', (string) $value);
    }
}

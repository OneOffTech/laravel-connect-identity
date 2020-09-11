<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Str;
use Oneofftech\Identities\Encryption\Encrypter;
use Oneofftech\Identities\Facades\IdentityCrypt;

class EncrypterTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('identities.old_key', Str::random(32));
    }

    public function test_value_encrypted_with_old_key_can_be_decrypted()
    {
        $encrypter = new Encrypter(config('identities.old_key'), 'AES-256-CBC');

        $encryptedWithOldKey = $encrypter->encrypt('test');

        $decrypted = IdentityCrypt::decrypt($encryptedWithOldKey);

        $this->assertEquals('test', $decrypted);
    }

    public function test_value_can_be_encrypted()
    {
        $encrypted = IdentityCrypt::encrypt('test');

        $decrypted = IdentityCrypt::decrypt($encrypted);

        $this->assertEquals('test', $decrypted);
    }
}

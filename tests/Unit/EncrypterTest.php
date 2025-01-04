<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Oneofftech\Identities\Encryption\Encrypter;
use Oneofftech\Identities\Facades\IdentityCrypt;
use Tests\TestCase;

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

        $encryptedWithOldKey = $encrypter->encryptString('test');

        $decrypted = IdentityCrypt::decryptString($encryptedWithOldKey);

        $this->assertEquals('test', $decrypted);
    }

    public function test_value_encrypted_with_old_base64_key_can_be_decrypted()
    {
        $key = Str::random(32);
        $this->app['config']->set('identities.old_key', 'base64:'.base64_encode($key));
        $encrypter = new Encrypter($key, 'AES-256-CBC');

        $encryptedWithOldKey = $encrypter->encryptString('test');

        $decrypted = IdentityCrypt::decryptString($encryptedWithOldKey);

        $this->assertEquals('test', $decrypted);
    }

    public function test_value_can_be_encrypted()
    {
        $encrypted = IdentityCrypt::encryptString('test');

        $decrypted = IdentityCrypt::decryptString($encrypted);

        $this->assertEquals('test', $decrypted);
    }
}

<?php

namespace Tests\Unit;

use Oneofftech\Identities\Encryption\Encrypter;
use Oneofftech\Identities\Facades\IdentityCrypt;
use Tests\TestCase;

class IdentityCryptTest extends TestCase
{
    public function test_value_can_be_hashed()
    {
        $value = 'A value';
        $expected = 'af4ca8f543cdca304ca2345c4d80db84d004889804974328da71bf1c451a2c2d23a71e2c853909bf5596899e74bc82b2019d20fcc93850c4d6d86fd04a67bc5d';

        $result = IdentityCrypt::hash($value);

        $this->assertEquals($expected, $result);
    }

    public function test_facade_return_encrypter_instance()
    {
        $this->assertInstanceOf(Encrypter::class, IdentityCrypt::getFacadeRoot());
    }
}

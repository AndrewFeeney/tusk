<?php

namespace App\Services;

use phpseclib3\Crypt\RSA as PhpSecLib3RSA;
use phpseclib3\Crypt\RSA\PrivateKey;

class RSA
{
    public function generateKey()
    {
        return PhpSecLib3RSA::createKey();
    }

    public function sign(PrivateKey $key, string $string)
    {
        return $key->sign($string);
    }
}

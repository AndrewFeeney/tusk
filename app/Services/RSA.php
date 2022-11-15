<?php

namespace App\Services;
use phpseclib3\Crypt\RSA as PhpSecLib3RSA;

class RSA
{
    public function generateKey()
    {
        return PhpSecLib3RSA::createKey();
    }
}

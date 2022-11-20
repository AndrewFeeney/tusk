<?php

namespace App\Domain;

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey as RSAPrivateKey;
use phpseclib3\Crypt\RSA\PublicKey;

class PrivateKey
{
    protected RSAPrivateKey $privateKey;

    public function __construct(RSAPrivateKey $privateKey)
    {
        $this->privateKey = $privateKey;
    }

    public static function generate(): self
    {
        return new self(RSA::createKey());
    }

    public function sign(string $message)
    {
        return $this->privateKey->sign($message);
    }

    public function publicKey(): PublicKey
    {
        return $this->privateKey->getPublicKey();
    }
}

<?php

namespace App\Domain;

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey as RSAPrivateKey;
use phpseclib3\Crypt\RSA\PublicKey;

class PrivateKey implements Signatory
{
    protected RSAPrivateKey $privateKey;
    protected string $keyId;

    public function __construct(RSAPrivateKey $privateKey, string $keyId)
    {
        $this->privateKey = $privateKey;
        $this->keyId = $keyId;
    }

    public static function generate(string $keyId): self
    {
        return new self(RSA::createKey(), $keyId);
    }

    public function keyId(): string
    {
        return $this->keyId;
    }

    public function paddingType(): int
    {
        return RSA::SIGNATURE_PKCS1;
    }

    public function sign(string $message)
    {
        return $this->privateKey->sign($message);
    }

    public function publicKey(): PublicKey
    {
        return $this->privateKey->getPublicKey();
    }

    public function keyString(): string
    {
        return $this->privateKey->__toString();
    }

    public function __toString()
    {
        return $this->keyString();
    }
}

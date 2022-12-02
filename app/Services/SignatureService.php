<?php

namespace App\Services;

use App\Domain\Signable;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class SignatureService
{
    public function sign(Signable $signable, string $privateKeyString, $paddingType = RSA::SIGNATURE_PSS): string
    {
        return $this->signStringWithPrivateKey($signable->signingString(), $privateKeyString, $paddingType);
    }

    public function signStringWithPrivateKey(string $stringToBeSigned, string $privateKeyString, $paddingType = RSA::SIGNATURE_PSS): string
    {
        $privateKey = PublicKeyLoader::load($privateKeyString)
            ->withPadding($paddingType);

        return base64_encode($privateKey->sign($stringToBeSigned));
    }

    public function verifySignatureWithPublicKey(string $signature, string $unsignedString, string $publicKeyString, $paddingType = RSA::SIGNATURE_PSS): bool
    {
        $publicKey = PublicKeyLoader::load($publicKeyString)
            ->withPadding($paddingType);

        $decodedSignature = base64_decode($signature, true);

        $signatureIsValid = $publicKey->verify($unsignedString, $decodedSignature);

        return $signatureIsValid;
    }
}

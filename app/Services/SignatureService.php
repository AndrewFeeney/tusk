<?php

namespace App\Services;

use App\Domain\Signable;
use App\Domain\Signatory;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class SignatureService
{
    public function sign(Signable $signable, Signatory $signatory)
    {
        return $this->signStringWithPrivateKey($signable->signingString(), $signatory->keyString(), $signatory->paddingType());
    }

    public function verifySignatureWithPublicKey(string $signature, string $unsignedString, string $publicKeyString, $paddingType = RSA::SIGNATURE_PSS): bool
    {
        $publicKey = PublicKeyLoader::load($publicKeyString)
            ->withPadding($paddingType);

        $decodedSignature = base64_decode($signature, true);

        $signatureIsValid = $publicKey->verify($unsignedString, $decodedSignature);

        return $signatureIsValid;
    }

    private function signStringWithPrivateKey(string $stringToBeSigned, string $privateKeyString, $paddingType = RSA::SIGNATURE_PSS): string
    {
        $privateKey = PublicKeyLoader::load($privateKeyString)
            ->withPadding($paddingType);

        return base64_encode($privateKey->sign($stringToBeSigned));
    }
}

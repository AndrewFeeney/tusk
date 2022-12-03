<?php

namespace App\Services;

use App\Domain\Signable;
use App\Domain\Signatory;
use App\Domain\VerifiableSignatory;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class SignatureService
{
    public function sign(Signable $signable, Signatory $signatory)
    {
        return $this->signStringWithPrivateKey($signable->signingString(), $signatory->keyString(), $signatory->paddingType());
    }

    public function verifySignature(string $signature, string $unsignedString, VerifiableSignatory $signatory): bool
    {
        $publicKey = PublicKeyLoader::load($signatory->publicKeyString())
            ->withPadding($signatory->paddingType());

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

<?php

namespace App\Services;

use App\Domain\Actor;
use App\Domain\HttpHeader;
use App\Domain\Request;
use App\Domain\Signable;
use App\Domain\Signatory;
use App\Domain\VerifiableSignatory;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class SignatureService
{
    public function signRequest(Request $request, Signatory $signatory): Request
    {
        $signature = $this->sign($request, $signatory);
        $signedHeaders = implode(' ', $request->headersToSign());

        $components = [
            "keyId=\"{$signatory->keyId()}\"",
            "algorithm=\"rsa-sha256\"",
            "headers=\"{$signedHeaders}\"",
            "signature=\"$signature\"",
        ];

        $header = new HttpHeader('Signature', implode(',', $components));

        $request->headers()->push($header);

        return $request;
    }

    public function sign(Signable $signable, Signatory $signatory): string
    {
        return $this->signStringWithPrivateKey($signable->signingString(), $signatory->keyString(), $signatory->paddingType());
    }

    public function verifySignature(string $signature, string $unsignedString, VerifiableSignatory $signatory): bool
    {
        /** @var phpseclib3\Crypt\RSA\PublicKey */
        $publicKey = PublicKeyLoader::load($signatory->publicKeyString());

        $publicKey = $publicKey->withPadding($signatory->paddingType());

        $decodedSignature = base64_decode($signature, true);

        $signatureIsValid = $publicKey->verify($unsignedString, $decodedSignature);

        return $signatureIsValid;
    }

    private function signStringWithPrivateKey(string $stringToBeSigned, string $privateKeyString, $paddingType = RSA::SIGNATURE_PSS): string
    {
        /** @var phpseclib3\Crypt\RSA\PrivateKey */
        $privateKey = PublicKeyLoader::load($privateKeyString);

        $privateKey = $privateKey->withPadding($paddingType);

        return base64_encode($privateKey->sign($stringToBeSigned));
    }
}

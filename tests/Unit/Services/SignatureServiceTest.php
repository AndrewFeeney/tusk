<?php

namespace Tests\Unit\Services;

use App\Domain\Signable;
use App\Services\SignatureService;
use phpseclib3\Crypt\RSA;
use Tests\TestCase;

/**
 * The hardcoded test values in this test case are taken from the draft document
 * at https://datatracker.ietf.org/doc/html/draft-cavage-http-signatures-08#section-2.1.1
 * which describes the mechanism used for signing HTTP requests.
 *
 * Presumably the keys were chosen by fair dice roll.
 **/
class SignatureServiceTest extends TestCase
{
    /** @test */
    public function it_can_sign_a_string_with_a_private_key()
    {
        $plainTextString = 'date: Thu, 05 Jan 2014 21:31:40 GMT';

        $testPrivateKey = file_get_contents(base_path('tests/Files/private.pem'));

        $signatureService = app()->make(SignatureService::class);

        $signature = $signatureService->signStringWithPrivateKey($plainTextString, $testPrivateKey, RSA::SIGNATURE_PKCS1);

        $this->assertSame(
            'jKyvPcxB4JbmYY4mByyBY7cZfNl4OW9HpFQlG7N4YcJPteKTu4MWCLyk+gIr0wDgqtLWf9NLpMAMimdfsH7FSWGfbMFSrsVTHNTk0rK3usrfFnti1dxsM4jl0kYJCKTGI/UWkqiaxwNiKqGcdlEDrTcUhhsFsOIo8VhddmZTZ8w=',
            $signature
        );
    }

    /** @test */
    public function it_can_verify_a_known_good_signature_for_a_given_string()
    {
        $signedString = 'date: Thu, 05 Jan 2014 21:31:40 GMT';
        $testPublicKey = file_get_contents(base_path('tests/Files/public.pem'));
        $knownGoodSignature = 'jKyvPcxB4JbmYY4mByyBY7cZfNl4OW9HpFQlG7N4YcJPteKTu4MWCLyk+gIr0wDgqtLWf9NLpMAMimdfsH7FSWGfbMFSrsVTHNTk0rK3usrfFnti1dxsM4jl0kYJCKTGI/UWkqiaxwNiKqGcdlEDrTcUhhsFsOIo8VhddmZTZ8w=';

        $signatureService = app()->make(SignatureService::class);
        $signatureIsValid = $signatureService->verifySignatureWithPublicKey($knownGoodSignature, $signedString, $testPublicKey, RSA::SIGNATURE_PKCS1);
        $this->assertTrue($signatureIsValid);
    }

    /** @test */
    public function it_can_sign_a_signable()
    {
        $testSignable = new class implements Signable {
            public function signingString(): string
            {
                return 'date: Thu, 05 Jan 2014 21:31:40 GMT';
            }
        };

        $testPrivateKey = file_get_contents(base_path('tests/Files/private.pem'));

        $signatureService = app()->make(SignatureService::class);

        $signature = $signatureService->sign($testSignable, $testPrivateKey, RSA::SIGNATURE_PKCS1);

        $this->assertSame(
            'jKyvPcxB4JbmYY4mByyBY7cZfNl4OW9HpFQlG7N4YcJPteKTu4MWCLyk+gIr0wDgqtLWf9NLpMAMimdfsH7FSWGfbMFSrsVTHNTk0rK3usrfFnti1dxsM4jl0kYJCKTGI/UWkqiaxwNiKqGcdlEDrTcUhhsFsOIo8VhddmZTZ8w=',
            $signature
        );
    }
}

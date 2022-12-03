<?php

namespace Tests\Unit\Services;

use App\Domain\Signable;
use App\Domain\Signatory;
use App\Domain\VerifiableSignatory;
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
    public function it_can_verify_a_known_good_signature_for_a_given_string()
    {
        $testSignable = new class implements Signable {
            public function signingString(): string
            {
                return 'date: Thu, 05 Jan 2014 21:31:40 GMT';
            }
        };

        $testVerifiableSignatory = new class implements VerifiableSignatory {
            public function keyId(): string {
                return 'test';
            }

            public function publickeyString(): string {
                return file_get_contents(base_path('tests/Files/public.pem'));
            }

            public function paddingType(): int {
                return RSA::SIGNATURE_PKCS1;
            }
        };

        $knownGoodSignature = 'jKyvPcxB4JbmYY4mByyBY7cZfNl4OW9HpFQlG7N4YcJPteKTu4MWCLyk+gIr0wDgqtLWf9NLpMAMimdfsH7FSWGfbMFSrsVTHNTk0rK3usrfFnti1dxsM4jl0kYJCKTGI/UWkqiaxwNiKqGcdlEDrTcUhhsFsOIo8VhddmZTZ8w=';

        $signatureService = app()->make(SignatureService::class);
        $signatureIsValid = $signatureService->verifySignature($knownGoodSignature, $testSignable->signingString(), $testVerifiableSignatory);
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

        $testPrivateKey = new class implements Signatory {
            public function keyId(): string {
                return 'test';
            }

            public function keyString(): string {
                return file_get_contents(base_path('tests/Files/private.pem'));
            }

            public function paddingType(): int {
                return RSA::SIGNATURE_PKCS1;
            }
        };

        $signatureService = app()->make(SignatureService::class);

        $signature = $signatureService->sign($testSignable, $testPrivateKey, RSA::SIGNATURE_PKCS1);

        $this->assertSame(
            'jKyvPcxB4JbmYY4mByyBY7cZfNl4OW9HpFQlG7N4YcJPteKTu4MWCLyk+gIr0wDgqtLWf9NLpMAMimdfsH7FSWGfbMFSrsVTHNTk0rK3usrfFnti1dxsM4jl0kYJCKTGI/UWkqiaxwNiKqGcdlEDrTcUhhsFsOIo8VhddmZTZ8w=',
            $signature
        );
    }
}

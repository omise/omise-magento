<?php
namespace Omise\Payment\Test\Unit\Gateway\Validator;

use Omise\Payment\Gateway\Validator\OmiseCaptureCommandResponseValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid as ErrorInvalid;
use Omise\Payment\Model\Api\Charge;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Omise\Payment\Gateway\Validator\OmiseCaptureCommandResponseValidator
 */
class OmiseCaptureCommandResponseValidatorTest extends TestCase
{
    private OmiseCaptureCommandResponseValidator $validator;

    protected function setUp(): void
    {
        // Bypass constructor
        $this->validator = (new ReflectionClass(OmiseCaptureCommandResponseValidator::class))
            ->newInstanceWithoutConstructor();
    }

    private function callValidateResponse(Charge $charge)
    {
        $reflection = new ReflectionClass($this->validator);
        $method = $reflection->getMethod('validateResponse');
        $method->setAccessible(true);

        return $method->invoke($this->validator, $charge);
    }

    /**
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::__construct
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::getMessage
     */
    public function testValidateResponseReturnsErrorInvalidWhenChargeFailed(): void
    {
        $charge = $this->createMock(Charge::class);
        $charge->method('isFailed')->willReturn(true);
        $charge->failure_code = 'failed_code';

        $result = $this->callValidateResponse($charge);

        $this->assertInstanceOf(ErrorInvalid::class, $result);
        $this->assertSame('failed_code', (string) $result->getMessage()); // <-- cast to string
    }

    public function testValidateResponseReturnsTrueWhenChargeSuccessful(): void
    {
        $charge = $this->createMock(Charge::class);
        $charge->method('isFailed')->willReturn(false);
        $charge->method('isSuccessful')->willReturn(true);

        $result = $this->callValidateResponse($charge);

        $this->assertTrue($result);
    }

    /**
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::__construct
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::getMessage
     */
    public function testValidateResponseReturnsErrorInvalidWhenChargeNotSuccessful(): void
    {
        $charge = $this->createMock(Charge::class);
        $charge->method('isFailed')->willReturn(false);
        $charge->method('isSuccessful')->willReturn(false);

        $result = $this->callValidateResponse($charge);

        $this->assertInstanceOf(ErrorInvalid::class, $result);
        $this->assertStringContainsString(
            'Payment failed, invalid payment status',
            (string) $result->getMessage() // <-- cast to string
        );
    }
}

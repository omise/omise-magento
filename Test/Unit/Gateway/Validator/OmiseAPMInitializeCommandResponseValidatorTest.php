<?php
namespace Omise\Payment\Test\Unit\Gateway\Validator;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Gateway\Validator\OmiseAPMInitializeCommandResponseValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid as ErrorInvalid;
use Omise\Payment\Model\Api\Charge;

/**
 * @covers \Omise\Payment\Gateway\Validator\OmiseAPMInitializeCommandResponseValidator
 */
class OmiseAPMInitializeCommandResponseValidatorTest extends TestCase
{
    /**
     * @var OmiseAPMInitializeCommandResponseValidator
     */
    private $validator;

    protected function setUp(): void
    {
        // Disable constructor to avoid Magento dependencies
        $this->validator = $this->getMockBuilder(OmiseAPMInitializeCommandResponseValidator::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::__construct
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::getMessage
     */
    public function testValidateResponseReturnsErrorWhenChargeFailed()
    {
        $charge = $this->createMock(Charge::class);
        $charge->failure_message = 'card declined';
        $charge->method('isFailed')->willReturn(true);

        $result = $this->invokeValidateResponse($charge);

        $this->assertInstanceOf(ErrorInvalid::class, $result);
        $this->assertStringContainsString('Payment failed. Card declined', $result->getMessage());
    }

    /**
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::__construct
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::getMessage
     */
    public function testValidateResponseReturnsTrueWhenChargeAwaitPayment()
    {
        $charge = $this->createMock(Charge::class);
        $charge->method('isFailed')->willReturn(false);
        $charge->method('isAwaitPayment')->willReturn(true);

        $result = $this->invokeValidateResponse($charge);

        $this->assertTrue($result);
    }

    /**
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::__construct
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::getMessage
     */
    public function testValidateResponseReturnsErrorWhenChargeInvalidStatus()
    {
        $charge = $this->createMock(Charge::class);
        $charge->method('isFailed')->willReturn(false);
        $charge->method('isAwaitPayment')->willReturn(false);

        $result = $this->invokeValidateResponse($charge);

        $this->assertInstanceOf(ErrorInvalid::class, $result);
        $this->assertStringContainsString('Payment failed, invalid payment status', $result->getMessage());
    }

    /**
     * Helper to call protected validateResponse method
     *
     * @param Charge $charge
     * @return mixed
     */
    private function invokeValidateResponse(Charge $charge)
    {
        $reflection = new \ReflectionClass($this->validator);
        $method = $reflection->getMethod('validateResponse');
        $method->setAccessible(true);

        return $method->invoke($this->validator, $charge);
    }
}
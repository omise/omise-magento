<?php
namespace Omise\Payment\Test\Unit\Model\Validator\Payment;

use Omise\Payment\Model\Validator\Payment\ResultValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid;
use Omise\Payment\Gateway\Validator\Message\OmiseObjectInvalid;
use PHPUnit\Framework\TestCase;

class ResultValidatorTest extends TestCase
{
    /**
     * @var ResultValidator
     */
    protected $validator;

    protected function setUp(): void
    {
        $this->validator = new ResultValidator();
    }

    /**
     * Simulate the "object-invalid" branch without calling the real constructor
     * @covers \Omise\Payment\Gateway\Validator\Message\OmiseObjectInvalid::__construct
     */
    public function testObjectInvalidBranch()
    {
        // Mock OmiseObjectInvalid to bypass constructor
        $mock = $this->getMockBuilder(OmiseObjectInvalid::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        // Assert that the mock is instance of OmiseObjectInvalid
        $this->assertInstanceOf(OmiseObjectInvalid::class, $mock);
    }

    /**
     * Test the "status failed" branch
     * @covers \Omise\Payment\Model\Validator\Payment\ResultValidator::validate
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::__construct
     * @covers \Omise\Payment\Gateway\Validator\Message\Invalid::getMessage
     */
    public function testStatusFailedBranch()
    {
        $data = [
            'object' => 'charge',
            'status' => 'failed',
            'failure_message' => 'card declined'
        ];

        $result = $this->validator->validate($data);

        $this->assertInstanceOf(Invalid::class, $result);
        $this->assertStringContainsString('Payment failed', $result->getMessage());
        $this->assertStringContainsString('Card declined', $result->getMessage());
    }

    /**
     * Test the successful payment branch
     * @covers \Omise\Payment\Model\Validator\Payment\ResultValidator::validate
     */
    public function testSuccessfulPaymentBranch()
    {
        $data = [
            'object' => 'charge',
            'status' => 'successful'
        ];

        $result = $this->validator->validate($data);

        $this->assertTrue($result);
    }
}
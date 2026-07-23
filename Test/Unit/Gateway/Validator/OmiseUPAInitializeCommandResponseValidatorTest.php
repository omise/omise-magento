<?php

namespace Omise\Payment\Test\Unit\Gateway\Validator;

use PHPUnit\Framework\TestCase;
use Magento\Payment\Gateway\Validator\Result;
use Omise\Payment\Gateway\Validator\OmiseUPAInitializeCommandResponseValidator;
use Omise\Payment\Model\Api\CheckoutSession;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Helper\RequestHelper;
use Omise\Payment\Helper\OmiseHelper;
use ReflectionClass;

class TestableOmiseUPAInitializeCommandResponseValidator extends OmiseUPAInitializeCommandResponseValidator
{
    public function __construct()
    {
        // Skip parent constructor
    }

    protected function createResult(
        $isValid,
        array $failsDescription = [],
        array $errorCodes = []
    ) {
        return new Result(
            $isValid,
            $failsDescription,
            $errorCodes
        );
    }
}

/**
 * @covers \Omise\Payment\Gateway\Validator\OmiseUPAInitializeCommandResponseValidator
 * @uses \Omise\Payment\Model\Api\BaseObject
 * @uses \Omise\Payment\Model\Api\CheckoutSession
 * @uses \Omise\Payment\Gateway\Validator\Message\Invalid
 */
class OmiseUPAInitializeCommandResponseValidatorTest extends TestCase
{
    /**
     * Create validator instance.
     */
    private function createValidator(): OmiseUPAInitializeCommandResponseValidator
    {
        return new TestableOmiseUPAInitializeCommandResponseValidator();
    }

    /**
     * Create CheckoutSession object with test data.
     */
    private function createSession(
        string $id,
        string $object
    ): CheckoutSession {

        $config = $this->createMock(Config::class);

        $requestHelper = $this->createMock(
            RequestHelper::class
        );

        $omiseHelper = $this->createMock(
            OmiseHelper::class
        );

        $session = new CheckoutSession(
            $config,
            $requestHelper,
            $omiseHelper
        );

        $reflection = new ReflectionClass($session);

        $property = $reflection->getProperty('object');

        $property->setAccessible(true);

        $property->setValue(
            $session,
            [
                'id' => $id,
                'object' => $object
            ]
        );

        return $session;
    }

    /**
     * Covers:
     * empty($id)
     */
    public function testInvalidWhenIdIsEmpty(): void
    {
        $validator = $this->createValidator();

        $session = $this->createSession(
            '',
            'checkout_session'
        );

        $result = $validator->validate([
            'response' => [
                'session' => $session
            ]
        ]);

        $this->assertFalse($result->isValid());
    }

    /**
     * Covers:
     * session is not CheckoutSession instance
     * @uses \Omise\Payment\Gateway\Validator\Message\ResponseInvalid
     */
    public function testInvalidWhenSessionIsNotCheckoutSessionInstance(): void
    {
        $validator = $this->createValidator();

        $result = $validator->validate([
            'response' => [
                'session' => new \stdClass()
            ]
        ]);

        $this->assertFalse($result->isValid());
    }
    
    /**
      * Covers:
      * $object != checkout_session
      */
    public function testInvalidWhenObjectIsWrong(): void
     {
         $validator = $this->createValidator();
         $session = $this->createSession(
             'sess_test_123',
             'not_checkout_session'
         );
         $result = $validator->validate([
             'response' => [
                 'session' => $session
             ]
         ]);
         $this->assertFalse($result->isValid());
     }

    /**
     * Covers:
     * successful validation path
     */
    public function testValidSession(): void
    {
        $validator = $this->createValidator();

        $session = $this->createSession(
            'sess_test_123',
            'checkout_session'
        );

        $result = $validator->validate([
            'response' => [
                'session' => $session
            ]
        ]);

        $this->assertTrue($result->isValid());
    }
}
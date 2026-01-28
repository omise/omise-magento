<?php

namespace Omise\Payment\Test\Unit\Gateway\Request;

use Omise\Payment\Gateway\Request\CreditCardAuthorizeCaptureBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Request\CreditCardAuthorizeCaptureBuilder
 */
class CreditCardAuthorizeCaptureBuilderTest extends TestCase
{
    /**
     * @covers ::build
     */
    public function testBuildReturnsCaptureTrue()
    {
        $builder = new CreditCardAuthorizeCaptureBuilder();

        $result = $builder->build([]);

        $this->assertSame(
            ['capture' => true],
            $result
        );
    }
}

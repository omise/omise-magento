<?php

namespace Omise\Payment\Test\Unit\Gateway\Request;

use Omise\Payment\Gateway\Request\CreditCardAuthorizeBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Request\CreditCardAuthorizeBuilder
 */
class CreditCardAuthorizeBuilderTest extends TestCase
{
    /**
     * @covers ::build
     */
    public function testBuildReturnsCaptureFalse()
    {
        $builder = new CreditCardAuthorizeBuilder();

        $result = $builder->build([]);

        $this->assertSame(
            ['capture' => false],
            $result
        );
    }
}
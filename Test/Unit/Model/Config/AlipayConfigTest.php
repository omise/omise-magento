<?php

namespace Omise\Payment\Test\Unit\Model\Config;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Config\Alipay;

class AlipayConfigTest extends TestCase
{
    /**
     * @covers Omise\Payment\Model\Config\Alipay
     */
    public function testConstants()
    {
        $this->assertEquals('omise_offsite_alipay', Alipay::CODE);
        $this->assertEquals('alipay', Alipay::ID);
    }
}

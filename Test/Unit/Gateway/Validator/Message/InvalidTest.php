<?php

namespace Omise\Payment\Test\Unit\Gateway\Validator\Message;

use Omise\Payment\Gateway\Validator\Message\Invalid;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Validator\Message\Invalid
 */
class InvalidTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getMessage
     */
    public function testGetMessageReturnsPhrase(): void
    {
        $text = 'Invalid payment';
        $invalid = new Invalid($text);

        $phrase = $invalid->getMessage();

        $this->assertInstanceOf(Phrase::class, $phrase);
        $this->assertEquals($text, (string)$phrase);
    }
}
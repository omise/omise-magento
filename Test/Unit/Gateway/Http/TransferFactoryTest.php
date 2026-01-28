<?php

namespace Omise\Payment\Test\Unit\Gateway\Http;

use Omise\Payment\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Http\TransferFactory
 */
class TransferFactoryTest extends TestCase
{
    private $transferBuilderMock;
    private $transferMock;

    protected function setUp(): void
    {
        // Mock TransferInterface returned by TransferBuilder
        $this->transferMock = $this->createMock(TransferInterface::class);

        // Mock TransferBuilder
        $this->transferBuilderMock = $this->createMock(TransferBuilder::class);
        $this->transferBuilderMock
            ->method('setBody')
            ->willReturnSelf(); // chainable
        $this->transferBuilderMock
            ->method('build')
            ->willReturn($this->transferMock);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $transferFactory = new TransferFactory($this->transferBuilderMock);
        $this->assertInstanceOf(TransferFactory::class, $transferFactory);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    public function testCreateReturnsTransferInterface(): void
    {
        // Instantiate here to cover __construct for this test as well
        $transferFactory = new TransferFactory($this->transferBuilderMock);

        $request = ['amount' => 1000, 'currency' => 'THB'];

        $transfer = $transferFactory->create($request);

        $this->assertSame($this->transferMock, $transfer);
    }
}
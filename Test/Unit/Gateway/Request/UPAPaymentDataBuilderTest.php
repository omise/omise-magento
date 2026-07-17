<?php

namespace Omise\Payment\Test\Unit\Gateway\Request;

use Magento\Framework\Locale\Resolver;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Omise\Payment\Gateway\Request\UPAPaymentDataBuilder;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseMoney;
use PHPUnit\Framework\TestCase;
use Omise\Payment\Test\Mock\InfoMock;
use Magento\Payment\Gateway\Data\PaymentDataObject;

class UPAPaymentDataBuilderTest extends TestCase
{
    private $money;
    private $omiseHelper;
    private $localeResolver;
    private $storeManager;
    private $urlBuilder;
    private $builder;

    protected function setUp(): void
    {
        $this->money = $this->createMock(OmiseMoney::class);
        $this->omiseHelper = $this->createMock(OmiseHelper::class);
        $this->localeResolver = $this->createMock(Resolver::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->urlBuilder = $this->createMock(UrlInterface::class);

        $this->builder = new UPAPaymentDataBuilder(
            $this->money,
            $this->omiseHelper,
            $this->localeResolver,
            $this->storeManager,
            $this->urlBuilder
        );
    }

    /**
     * @covers Omise\Payment\Gateway\Request\UPAPaymentDataBuilder
     */
    public function testBuildReturnsPayload(): void
    {
        $paymentDO = $this->createMock(PaymentDataObject::class);
        $payment = $this->getMockBuilder(InfoMock::class)->getMock();
        $order = $this->createMock(OrderAdapterInterface::class);
        $store = $this->createMock(StoreInterface::class);

    
        $payment->method('getMethod')
        ->willReturn('omise_upa');

        $paymentDO = new PaymentDataObject(
            $order,
            $payment
        );

        $order->method('getCurrencyCode')
            ->willReturn('THB');

        $order->method('getStoreId')
            ->willReturn(1);

        $order->method('getGrandTotalAmount')
            ->willReturn(100.00);

        $order->method('getOrderIncrementId')
            ->willReturn('100000001');

        $store->method('getName')
            ->willReturn('Default Store');

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with(1)
            ->willReturn($store);

        $this->omiseHelper->expects($this->once())
            ->method('getMethodId')
            ->with('omise_upa')
            ->willReturn('promptpay');

        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');

        $this->money->expects($this->once())
            ->method('setAmountAndCurrency')
            ->with(100.00, 'THB')
            ->willReturnSelf();

        $this->money->expects($this->once())
            ->method('toSubunit')
            ->willReturn(10000);

        $this->urlBuilder->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturnOnConsecutiveCalls(
                'https://example.com/complete',
                'https://example.com/cancel'
            );

        $this->omiseHelper->expects($this->exactly(2))
            ->method('getConfig')
            ->willReturnMap([
                ['upa_theme_color', 1, '#000000'],
                ['upa_text_color', 1, '#FFFFFF'],
            ]
        );
        $result = $this->builder->build([
            'payment' => $paymentDO
        ]);

        $this->assertEquals([
            'amount' => 10000,
            'currency' => 'THB',
            'order_id' => '100000001',
            'description' => 'Magento Order id 100000001',
            'payment_methods' => ['promptpay'],
            'redirect_urls' => [
                'complete_url' => 'https://example.com/complete',
                'cancel_url' => 'https://example.com/cancel',
            ],
            'metadata' => [
                'order_id' => '100000001',
                'store_id' => 1,
                'store_name' => 'Default Store'
            ],
            'style' => [
                'theme_color' => '#000000',
                'text_color' => '#FFFFFF'
            ],
            'is_upa' => true,
            'locale' => 'en'
        ], $result);
    }

    /**
     * @covers Omise\Payment\Gateway\Request\UPAPaymentDataBuilder
     */
    public function testBuildReturnsEmptyArrayWhenMethodIdIsEmpty(): void
    {
        $paymentDO = $this->createMock(PaymentDataObject::class);
        $payment = $this->createMock(InfoMock::class);
        $order = $this->createMock(OrderAdapterInterface::class);

        $payment->method('getMethod')
            ->willReturn('omise_upa');

        $paymentDO = new PaymentDataObject(
            $order,
            $payment
        );

        $payment->method('getMethod')
            ->willReturn('omise_upa');

        $order->method('getStoreId')
            ->willReturn(1);

        $this->storeManager->method('getStore')
            ->willReturn(
                $this->createMock(StoreInterface::class)
            );

        $this->omiseHelper->expects($this->once())
            ->method('getMethodId')
            ->willReturn('');

        $result = $this->builder->build([
            'payment' => $paymentDO
        ]);

        $this->assertSame([], $result);
    }

    /**
     * @covers Omise\Payment\Gateway\Request\UPAPaymentDataBuilder
     */
    public function testBuildWithoutLocale(): void
    {
        $paymentDO = $this->createMock(PaymentDataObject::class);
        $payment = $this->createMock(InfoMock::class);
        $order = $this->createMock(OrderAdapterInterface::class);
        $store = $this->createMock(StoreInterface::class);

        $payment->method('getMethod')
            ->willReturn('omise_upa');

        $paymentDO = new PaymentDataObject(
            $order,
            $payment
        );

        $order->method('getCurrencyCode')
            ->willReturn('THB');

        $order->method('getStoreId')
            ->willReturn(1);

        $order->method('getGrandTotalAmount')
            ->willReturn(100);

        $order->method('getOrderIncrementId')
            ->willReturn('100000001');

        $store->method('getName')
            ->willReturn('Default Store');

        $this->storeManager->method('getStore')
            ->willReturn($store);

        $this->omiseHelper->method('getMethodId')
            ->willReturn('promptpay');
        
            $this->omiseHelper->method('getConfig')
            ->willReturnMap([
                ['upa_theme_color', 1, '#000000'],
                ['upa_text_color', 1, '#FFFFFF'],
            ]
        );

        $this->localeResolver->method('getLocale')
            ->willReturn('');

        $this->money->method('setAmountAndCurrency')
            ->willReturnSelf();

        $this->money->method('toSubunit')
            ->willReturn(10000);

        $this->urlBuilder->method('getUrl')
            ->willReturn('https://example.com');

        $result = $this->builder->build([
            'payment' => $paymentDO
        ]);

        $this->assertArrayNotHasKey('locale', $result);
    }
}
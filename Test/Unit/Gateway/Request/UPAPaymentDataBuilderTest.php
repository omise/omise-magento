<?php

namespace Omise\Payment\Test\Unit\Gateway\Request;

use Magento\Framework\Locale\Resolver;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Store\Model\Store;
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
        $store = $this->createMock(Store::class);
        $order = $this->createConfiguredMock(
            OrderAdapterInterface::class,
            [
                'getCurrencyCode'    => 'THB',
                'getStoreId'         => 1,
                'getGrandTotalAmount'=> 100.00,
                'getOrderIncrementId'=> '100000001',
            ]
        );

        $payment->method('getMethod')
            ->willReturn('omise_upa');

        $paymentDO = new PaymentDataObject(
            $order,
            $payment
        );

        $store->method('getName')
            ->willReturn('Default Store');
        $store->method('getBaseUrl')
            ->willReturn('https://example.com/');

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

        $this->omiseHelper->expects($this->exactly(4))
            ->method('getConfig')
            ->willReturnMap([
                ['upa_theme_color', 1, '#000000'],
                ['upa_text_color', 1, '#FFFFFF'],
                ['dynamic_webhooks', 1, '1'],
                ['webhook_status', 1, '1'],
            ]);

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
            'webhooks' => ['https://example.com/omise/callback/webhook'],
            'is_upa' => true,
            'locale' => 'en'
        ], $result);
    }

    /**
     * @covers Omise\Payment\Gateway\Request\UPAPaymentDataBuilder
     */
    public function testBuildWithoutLocale(): void
    {
        $paymentDO = $this->createMock(PaymentDataObject::class);
        $payment = $this->createMock(InfoMock::class);
        $store = $this->createMock(Store::class);
        $order = $this->createConfiguredMock(
            OrderAdapterInterface::class,
            [
                'getCurrencyCode'    => 'THB',
                'getStoreId'         => 1,
                'getGrandTotalAmount'=> 100.00,
                'getOrderIncrementId'=> '100000001',
            ]
        );

        $payment->method('getMethod')
            ->willReturn('omise_upa');

        $paymentDO = new PaymentDataObject(
            $order,
            $payment
        );

        $store->method('getName')
            ->willReturn('Default Store');
        $store->method('getBaseUrl')
            ->willReturn('https://example.com/');

        $this->storeManager->method('getStore')
            ->willReturn($store);

        $this->omiseHelper->method('getMethodId')
            ->willReturn('promptpay');
        
        $this->omiseHelper->method('getConfig')
            ->willReturnMap([
                ['upa_theme_color', 1, '#000000'],
                ['upa_text_color', 1, '#FFFFFF'],
                ['dynamic_webhooks', 1, '1'],
                ['webhook_status', 1, '1'],
            ]);

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

    /**
     * @covers Omise\Payment\Gateway\Request\UPAPaymentDataBuilder
     */
    public function testBuildReturnsEmptyArrayWhenMethodIdIsEmpty(): void
    {
        $paymentDO = $this->createMock(PaymentDataObject::class);
        $payment = $this->createMock(InfoMock::class);
        $order = $this->createConfiguredMock(OrderAdapterInterface::class, [ 'getStoreId' => 1]);

        $payment->method('getMethod')
            ->willReturn('omise_upa');

        $paymentDO = new PaymentDataObject(
            $order,
            $payment
        );

        $this->storeManager->method('getStore')
            ->willReturn(
                $this->createMock(Store::class)
            );

        $this->omiseHelper->expects($this->once())
            ->method('getMethodId')
            ->willReturn('');

        $result = $this->builder->build([
            'payment' => $paymentDO
        ]);

        $this->assertSame([], $result);
    }
}

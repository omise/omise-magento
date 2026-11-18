<?php

namespace Omise\Payment\Test\Unit;

use Magento\Framework\App\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use PHPUnit\Framework\TestCase;
use Omise\Payment\Gateway\Request\PaymentDataBuilder;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Model\Config\Cc;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Model\Config\Promptpay;
use Omise\Payment\Model\Capability;

class PaymentDataBuilderTest extends TestCase
{
    private $omiseMoneyMock;
    private $ccConfigMock;
    private $paymentDataMock;
    private $paymentMock;
    private $orderMock;
    private $factoryMock;
    private $configMock;
    private $storeManagerMock;
    private $storeMock;
    private $capability;
    private $omiseHelperMock;

    protected function setUp(): void
    {
        $this->factoryMock = $this->createMock(FactoryInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->omiseMoneyMock = $this->getMockBuilder(OmiseMoney::class)->disableOriginalConstructor()->getMock();
        $this->ccConfigMock = $this->getMockBuilder(Cc::class)->disableOriginalConstructor()->getMock();
        $this->paymentMock = $this->createMock(OrderPaymentInterface::class);
        $this->paymentDataMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->getMockBuilder(Store::class)->disableOriginalConstructor()->getMock();
        $this->capability = $this->getMockBuilder(Capability::class)->disableOriginalConstructor()->getMock();
        $this->omiseHelperMock = $this->getMockBuilder(OmiseHelper::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @covers Omise\Payment\Gateway\Request\PaymentDataBuilder
     */
    public function testConstants()
    {
        $this->assertEquals('webhook_endpoints', PaymentDataBuilder::WEBHOOKS_ENDPOINT);
        $this->assertEquals('amount', PaymentDataBuilder::AMOUNT);
        $this->assertEquals('currency', PaymentDataBuilder::CURRENCY);
        $this->assertEquals('description', PaymentDataBuilder::DESCRIPTION);
        $this->assertEquals('metadata', PaymentDataBuilder::METADATA);
        $this->assertEquals('zero_interest_installments', PaymentDataBuilder::ZERO_INTEREST_INSTALLMENTS);
    }

    /**
     * @dataProvider buildDataProvider
     * @covers Omise\Payment\Gateway\Request\PaymentDataBuilder
     */
    public function testBuild($paymentMethod, $expectedMetadata)
    {
        $amount = 1000;
        $currency = 'THB';
        $orderId = 123;
        $storeId = 1;
        $storeName = 'opn-store';
        $storeBaseUrl = 'https://omise.co/';
        new ObjectManager($this->factoryMock, $this->configMock);

        $this->paymentMock->method('getMethod')->willReturn($paymentMethod);
        $this->paymentMock->method('getAdditionalInformation')->willReturn('installment_mbb');

        $this->ccConfigMock->method('isDynamicWebhooksEnabled')->willReturn(true);

        $this->omiseMoneyMock->method('setAmountAndCurrency')->willReturn($this->omiseMoneyMock);
        $this->omiseMoneyMock->method('toSubunit')->willReturn($amount * 100);

        $this->storeMock->method('getName')->willReturn($storeName);
        $this->storeMock->method('getBaseUrl')->willReturn($storeBaseUrl);

        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);
        $this->configMock->method('getPreference');
        $this->factoryMock->method('create')->willReturn($this->storeManagerMock);

        $this->orderMock->method('getCurrencyCode')->willReturn($currency);
        $this->orderMock->method('getGrandTotalAmount')->willReturn($amount);
        $this->orderMock->method('getOrderIncrementId')->willReturn($orderId);
        $this->orderMock->method('getStoreId')->willReturn($storeId);
        $this->omiseHelperMock->method('getDescription')->with($this->orderMock)->willReturn('Magento Order id 123');

        $this->paymentDataMock->method('getOrder')->willReturn($this->orderMock);
        $this->paymentDataMock->method('getPayment')->willReturn($this->paymentMock);

        $model = new PaymentDataBuilder(
            $this->ccConfigMock,
            $this->omiseMoneyMock,
            $this->capability,
            $this->omiseHelperMock
        );
        $result = $model->build(['payment' => $this->paymentDataMock]);

        $this->assertEquals(100000, $result['amount']);
        $this->assertEquals('THB', $result['currency']);
        $this->assertEquals([
            'https://omise.co/omise/callback/webhook'
        ], $result['webhook_endpoints']);
        $this->assertEquals($expectedMetadata, $result['metadata']);

        if ($paymentMethod === Installment::CODE) {
            $this->assertEquals(true, $result['zero_interest_installments']);
        }
    }

    /**
     * Data provider for testBuild method
     */
    public function buildDataProvider()
    {
        return [
            [
                'paymentMethod' => Cc::CODE,
                'expectedMetadata' => [
                    'order_id' => 123,
                    'store_id' => 1,
                    'store_name' => 'opn-store',
                ],
            ],
            [
                'paymentMethod' => Installment::CODE,
                'expectedMetadata' => [
                    'order_id' => 123,
                    'store_id' => 1,
                    'store_name' => 'opn-store',
                ],
            ],
            [
                'paymentMethod' => Promptpay::CODE,
                'expectedMetadata' => [
                    'order_id' => 123,
                    'store_id' => 1,
                    'store_name' => 'opn-store',
                ],
            ],
        ];
    }
}

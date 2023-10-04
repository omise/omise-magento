<?php

namespace Omise\Payment\Test\Unit;

use Magento\Framework\App\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Omise\Payment\Gateway\Request\PaymentDataBuilder;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Model\Config\Cc;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Model\Config\Promptpay;

class PaymentDataBuilderTest extends TestCase
{
    private $omiseMoneyMock;
    private $ccConfigMock;
    private $configModelMock;
    private $paymentDataMock;
    private $paymentMock;
    private $orderMock;
    private $factoryMock;
    private $configMock;
    private $storeManagerMock;
    private $storeMock;

    protected function setUp(): void
    {
        $this->factoryMock = m::mock(FactoryInterface::class);
        $this->configMock = m::mock(ConfigInterface::class);
        $this->omiseMoneyMock = m::mock(OmiseMoney::class);
        $this->ccConfigMock = m::mock(Cc::class);
        $this->paymentMock = m::mock(OrderPaymentInterface::class);
        $this->paymentDataMock = m::mock(PaymentDataObjectInterface::class);
        $this->orderMock = m::mock(OrderInterface::class);
        $this->storeManagerMock =  m::mock(StoreManagerInterface::class);
        $this->storeMock =  m::mock(StoreInterface::class);
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
        $secureFormEnabled = true;

        new ObjectManager($this->factoryMock, $this->configMock);

        $this->paymentMock->shouldReceive('getMethod')->andReturn($paymentMethod);
        $this->paymentMock->shouldReceive('getAdditionalInformation')->andReturn('installment_mbb');

        $this->ccConfigMock->shouldReceive('getSecureForm')->andReturn($secureFormEnabled);
        $this->ccConfigMock->shouldReceive('isWebhookEnabled')->andReturn(true);

        $this->omiseMoneyMock->shouldReceive('setAmountAndCurrency')->andReturn($this->omiseMoneyMock);
        $this->omiseMoneyMock->shouldReceive('toSubunit')->andReturn($amount * 100);

        $this->storeMock->shouldReceive('getName')->andReturn($storeName);
        $this->storeMock->shouldReceive('getBaseUrl')->andReturn($storeBaseUrl);

        $this->storeManagerMock->shouldReceive('getStore')->andReturn($this->storeMock);
        $this->configMock->shouldReceive('getPreference');
        $this->factoryMock->shouldReceive('create')->andReturn($this->storeManagerMock);

        $this->orderMock->shouldReceive('getCurrencyCode')->andReturn($currency);
        $this->orderMock->shouldReceive('getGrandTotalAmount')->andReturn($amount);
        $this->orderMock->shouldReceive('getOrderIncrementId')->andReturn($orderId);
        $this->orderMock->shouldReceive('getStoreId')->andReturn($storeId);

        $this->paymentDataMock->shouldReceive('getOrder')->andReturn($this->orderMock);
        $this->paymentDataMock->shouldReceive('getPayment')->andReturn($this->paymentMock);

        $model = new PaymentDataBuilder($this->ccConfigMock, $this->omiseMoneyMock);
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
                    'secure_form_enabled' => true
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

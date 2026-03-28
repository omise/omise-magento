<?php

namespace Omise\Payment\Test\Unit\Helper;

use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Config\Truemoney;
use Omise\Payment\Model\Config\Paynow;
use Omise\Payment\Model\Config\CcGooglePay;
use Omise\Payment\Model\Config\Conveniencestore;

class OmiseHelperTest extends \PHPUnit\Framework\TestCase
{
    protected $headerMock;

    protected $configMock;

    protected $model;

    private $authorizeUri = 'https://somefakeuri.com/redirect';

    /**
     * This function is called before the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function setUp(): void
    {
        $this->configMock = $this->createMock('Omise\Payment\Model\Config\Config');
        $this->model = new OmiseHelper($this->configMock);
    }

    /**
     * This function is called after the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function tearDown(): void
    {
    }

    /**
     * Test the function isPayableByImageCode() returns true when correct code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isPayableByImageCodeReturnsTrueWhenCorrectPaymentCodeIsPassed()
    {
        $isPayableByImageCode = $this->model->isPayableByImageCode(Paynow::CODE);
        $this->assertTrue($isPayableByImageCode);
    }

    /**
     * Test the function isPayableByImageCode() returns false when invalid code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isPayableByImageCodeReturnsFalseWhenWrongPaymentCodeIsPassed()
    {
        $isPayableByImageCode = $this->model->isPayableByImageCode(CcGooglePay::CODE);
        $this->assertFalse($isPayableByImageCode);
    }

    /**
     * Test the function isOfflinePaymentMethod() returns true when correct code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isOfflinePaymentMethodReturnsTrueWhenWrongPaymentCodeIsPassed()
    {
        $isOfflinePaymentMethod = $this->model->isOfflinePaymentMethod(Conveniencestore::CODE);
        $this->assertTrue($isOfflinePaymentMethod);
    }

    /**
     * Test the function isOfflinePaymentMethod() returns false when invalid code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     */
    public function testIsOfflinePaymentMethodReturnsFalseWhenWrongPaymentCodeIsPassed()
    {
        $isOfflinePaymentMethod = $this->model->isOfflinePaymentMethod(CcGooglePay::CODE);
        $this->assertFalse($isOfflinePaymentMethod);
    }

    /**
     * Test the function isOffsitePaymentMethod() returns true when correct code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     */
    public function isOffsitePaymentMethodReturnsTrueWhenWrongPaymentCodeIsPassed()
    {
        $isOffsitePaymentMethod = $this->model->isOffsitePaymentMethod(Truemoney::CODE);
        $this->assertTrue($isOffsitePaymentMethod);
    }

    /**
     * Test the function isOffsitePaymentMethod() returns false when invalid code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isOffsitePaymentMethodReturnsFalseWhenWrongPaymentCodeIsPassed()
    {
        $isOffsitePaymentMethod = $this->model->isOffsitePaymentMethod(CcGooglePay::CODE);
        $this->assertFalse($isOffsitePaymentMethod);
    }

    /**
     * Test the function isOmisePayment() return true whe
     * correct payment code is passed
     *
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isOmisePaymentReturnsTrueWhenCorrectPaymentCodeIsPassed()
    {
        $isOmisePayment = $this->model->isOmisePayment(CcGooglePay::CODE);
        $this->assertTrue($isOmisePayment);
    }

    /**
     * Test the function whether isCreditCardPaymentMethod() returns false
     * when invalid code is passed
     *
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isCreditCardPaymentMethodReturnFalseIfWrongPaymentCodeIsPassed()
    {
        $isCreditCardPaymentMethod = $this->model->isCreditCardPaymentMethod(Paynow::CODE);
        $this->assertFalse($isCreditCardPaymentMethod);
    }

    /**
     * Test the function is3DSecureEnabled() whether 3DS is enabled or not
     * by checking charge object
     *
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function is3DSecureEnabledReturnsTrue()
    {
        $charge = (object)[
            'status' => 'pending',
            'authorized' => false,
            'paid' => false,
            'authorize_uri' => $this->authorizeUri
        ];

        $result = $this->model->is3DSecureEnabled($charge);

        $this->assertTrue($result);
    }

    /**
     * Test the function is3DSecureEnabled() returns false if the value of
     * any one properties of charge does not match the condition
     *
     * @dataProvider chargeProvider
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function is3DSecureEnabledReturnsFalse($charge)
    {
        $result = $this->model->is3DSecureEnabled($charge);
        $this->assertFalse($result);
    }

    public function chargeProvider()
    {
        return [
            [(object)[
                'status' => 'canceled',
                'authorized' => false,
                'paid' => false,
                'authorize_uri' => $this->authorizeUri
            ]],
            [(object)[
                'status' => 'pending',
                'authorized' => true,
                'paid' => false,
                'authorize_uri' => $this->authorizeUri
            ]],
            [(object)[
                'status' => 'pending',
                'authorized' => false,
                'paid' => true,
                'authorize_uri' => $this->authorizeUri
            ]],
            [(object)[
                'status' => 'pending',
                'authorized' => false,
                'paid' => false,
                'authorize_uri' => ''
            ]]
        ];
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper::__construct
     * @covers \Omise\Payment\Helper\OmiseHelper::hasShopeepayFailed
     */
    public function testHasShopeepayFailed()
    {
        // Case 1: Shopeepay method and charge failed => should return true
        $this->assertTrue($this->model->hasShopeepayFailed('omise_offsite_shopeepay', false));

        // Case 2: Shopeepay method and charge successful => should return false
        $this->assertFalse($this->model->hasShopeepayFailed('omise_offsite_shopeepay', true));

        // Case 3: Not Shopeepay method => should return false regardless of charge
        $this->assertFalse($this->model->hasShopeepayFailed('omise_cc', false));
        $this->assertFalse($this->model->hasShopeepayFailed('omise_cc', true));
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper::__construct
     * @covers \Omise\Payment\Helper\OmiseHelper::getOmiseCodeByOmiseId
     */
    public function testGetOmiseCodeByOmiseId()
    {
        // Case 1: $name exists in the map
        $knownId = \Omise\Payment\Model\Config\Cc::ID; // actual key from helper
        $expectedCode = \Omise\Payment\Model\Config\Cc::CODE; // 'omise_cc'

        $this->assertEquals($expectedCode, $this->model->getOmiseCodeByOmiseId($knownId));

        // Case 2: $name does not exist
        $unknownId = 'unknown_id';
        $this->assertNull($this->model->getOmiseCodeByOmiseId($unknownId));
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper::__construct
     * @covers \Omise\Payment\Helper\OmiseHelper::getOmiseLabelByOmiseCode
     */
    public function testGetOmiseLabelByOmiseCode()
    {
        // Case 1: $code exists in the map
        $knownCode = \Omise\Payment\Model\Config\Cc::CODE; // 'omise_cc'
        $expectedLabel = "Credit Card Payment";

        $this->assertEquals($expectedLabel, $this->model->getOmiseLabelByOmiseCode($knownCode));

        // Case 2: $code does not exist
        $unknownCode = 'unknown_code';
        $this->assertNull($this->model->getOmiseLabelByOmiseCode($unknownCode));
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper::__construct
     * @covers \Omise\Payment\Helper\OmiseHelper::createInvoiceAndMarkAsPaid
     */
    public function testCreateInvoiceAndMarkAsPaid()
    {
        $chargeId = 'ch_123';

        // Mock invoice for existing invoice scenario
        $existingInvoiceMock = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);
        $existingInvoiceMock->method('setTransactionId')->willReturnSelf();
        $existingInvoiceMock->method('pay')->willReturnSelf();
        $existingInvoiceMock->method('save')->willReturnSelf();

        // Case 1: isCapture = false -> returns null
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->assertNull($this->model->createInvoiceAndMarkAsPaid($orderMock, $chargeId, false));

        // Case 2: order has invoices & status is pending -> uses last invoice
        $invoiceCollectionMock = $this->createMock(\Magento\Framework\Data\Collection::class);
        $invoiceCollectionMock->method('getLastItem')->willReturn($existingInvoiceMock);

        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->method('hasInvoices')->willReturn(true);
        $orderMock->method('getInvoiceCollection')->willReturn($invoiceCollectionMock);

        $this->configMock->method('getSendInvoiceAtOrderStatus')
        ->willReturn(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);

        $resultInvoice = $this->model->createInvoiceAndMarkAsPaid($orderMock, $chargeId, true);
        $this->assertSame($existingInvoiceMock, $resultInvoice);

        // Case 3: order has no invoices -> creates new invoice
        $newInvoiceMock = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);
        $newInvoiceMock->method('register')->willReturnSelf();
        $newInvoiceMock->method('setTransactionId')->willReturnSelf();
        $newInvoiceMock->method('pay')->willReturnSelf();
        $newInvoiceMock->method('save')->willReturnSelf();

        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->method('hasInvoices')->willReturn(false);
        $orderMock->method('prepareInvoice')->willReturn($newInvoiceMock);
        $orderMock->expects($this->once())->method('addRelatedObject')->with($newInvoiceMock)->willReturnSelf();

        $this->configMock->method('getSendInvoiceAtOrderStatus')->willReturn('other_status');

        $resultInvoice = $this->model->createInvoiceAndMarkAsPaid($orderMock, $chargeId, true);
        $this->assertSame($newInvoiceMock, $resultInvoice);
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper::__construct
     * @covers \Omise\Payment\Helper\OmiseHelper::getOrderChargeId
     * @covers \Omise\Payment\Helper\OmiseHelper::isOrderOmisePayment
     */
    public function testGetOrderChargeId()
    {
        $chargeId = 'ch_123';

        // Mock payment
        $paymentMock = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $paymentMock->method('getAdditionalInformation')
            ->with('charge_id')
            ->willReturn($chargeId);

        // Mock order
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->method('getPayment')->willReturn($paymentMock);

        // Helper instance
        $helperMock = $this->getMockBuilder(\Omise\Payment\Helper\OmiseHelper::class)
            ->setConstructorArgs([$this->configMock])
            ->onlyMethods(['isOrderOmisePayment'])
            ->getMock();

        // Case 1: Order is an Omise payment
        $helperMock->method('isOrderOmisePayment')->willReturn(true);
        $this->assertEquals($chargeId, $helperMock->getOrderChargeId($orderMock));

        // Case 2: Order is NOT an Omise payment
        $helperMock = $this->getMockBuilder(\Omise\Payment\Helper\OmiseHelper::class)
            ->setConstructorArgs([$this->configMock])
            ->onlyMethods(['isOrderOmisePayment'])
            ->getMock();
        $helperMock->method('isOrderOmisePayment')->willReturn(false);
        $this->assertNull($helperMock->getOrderChargeId($orderMock));
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper::__construct
     * @covers \Omise\Payment\Helper\OmiseHelper::canOrderStatusAutoSync
     * @covers \Omise\Payment\Helper\OmiseHelper::isOrderOmisePayment
     */
    public function testCanOrderStatusAutoSync()
    {
        // Mock order
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);

        // Helper mock to stub isOrderOmisePayment
        $helperMock = $this->getMockBuilder(\Omise\Payment\Helper\OmiseHelper::class)
            ->setConstructorArgs([$this->configMock])
            ->onlyMethods(['isOrderOmisePayment'])
            ->getMock();

        // Case 1: isOrderOmisePayment returns true
        $helperMock->method('isOrderOmisePayment')->willReturn(true);
        $this->assertTrue($helperMock->canOrderStatusAutoSync($orderMock));

        // Case 2: isOrderOmisePayment returns false
        $helperMock = $this->getMockBuilder(\Omise\Payment\Helper\OmiseHelper::class)
            ->setConstructorArgs([$this->configMock])
            ->onlyMethods(['isOrderOmisePayment'])
            ->getMock();
        $helperMock->method('isOrderOmisePayment')->willReturn(false);
        $this->assertFalse($helperMock->canOrderStatusAutoSync($orderMock));
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper::__construct
     * @covers \Omise\Payment\Helper\OmiseHelper::isOrderOmisePayment
     */
    public function testIsOrderOmisePayment()
    {
        // Case 1: method code contains "omise" -> should return true
        $methodMock1 = $this->createMock(\Magento\Payment\Model\MethodInterface::class);
        $methodMock1->method('getCode')->willReturn('omise_cc');

        $paymentMock1 = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $paymentMock1->method('getMethodInstance')->willReturn($methodMock1);

        $orderMock1 = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock1->method('getPayment')->willReturn($paymentMock1);

        $this->assertTrue($this->model->isOrderOmisePayment($orderMock1));

        // Case 2: method code does NOT contain "omise" -> should return false
        $methodMock2 = $this->createMock(\Magento\Payment\Model\MethodInterface::class);
        $methodMock2->method('getCode')->willReturn('paypal_express');

        $paymentMock2 = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $paymentMock2->method('getMethodInstance')->willReturn($methodMock2);

        $orderMock2 = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock2->method('getPayment')->willReturn($paymentMock2);

        $this->assertFalse($this->model->isOrderOmisePayment($orderMock2));
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper::__construct
     * @covers \Omise\Payment\Helper\OmiseHelper::getConfig
     */
    public function testGetConfig()
    {
        $fieldId = 'active';
        $expectedValue = '1';
        $path = 'payment/omise/' . $fieldId;

        // Mock ScopeConfigInterface
        $scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo($path), $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE))
            ->willReturn($expectedValue);

        // Create helper with the mocked scopeConfig
        $helper = $this->getMockBuilder(\Omise\Payment\Helper\OmiseHelper::class)
            ->setConstructorArgs([$this->configMock])
            ->onlyMethods([])
            ->getMock();

        // Inject scopeConfig mock (property is protected)
        $reflection = new \ReflectionClass($helper);
        $property = $reflection->getProperty('scopeConfig');
        $property->setAccessible(true);
        $property->setValue($helper, $scopeConfigMock);

        $this->assertEquals($expectedValue, $helper->getConfig($fieldId));
    }
}

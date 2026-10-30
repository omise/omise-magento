<?php

namespace Omise\Payment\Test\Unit\Helper;

use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Config\Truemoney;
use Omise\Payment\Model\Config\Paynow;
use Omise\Payment\Model\Config\CcGooglePay;
use Omise\Payment\Model\Config\Conveniencestore;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class OmiseHelperTest extends \PHPUnit\Framework\TestCase
{
    protected $headerMock;

    protected $configMock;

    protected $scopeConfig;

    protected $model;

    protected $omiseCheckoutSession;

    private $authorizeUri = 'https://somefakeuri.com/redirect';

    /**
     * This function is called before the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function setUp(): void
    {
        $this->configMock = $this->createMock('Omise\Payment\Model\Config\Config');
        $this->scopeConfig = $this->createMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->omiseCheckoutSession = $this->createMock('Omise\Payment\Model\Api\CheckoutSession');
        $this->model = new OmiseHelper($this->configMock, $this->scopeConfig, $this->omiseCheckoutSession);
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
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function getConfigReturnsValue()
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/omise/upa_theme_color',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('#1979C3');

        $this->assertEquals(
            '#1979C3',
            $this->model->getConfig('upa_theme_color')
        );
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isAllowUpaReturnsTrue()
    {
        $this->configMock->expects($this->once())
            ->method('getIsUpaFeatureFlagEnabled')
            ->willReturn(true);

        $this->assertTrue(
            $this->model->isAllowUpa(\Omise\Payment\Model\Config\Promptpay::CODE)
        );
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isAllowUpaReturnsFalseWhenFeatureDisabled()
    {
        $this->configMock->expects($this->once())
            ->method('getIsUpaFeatureFlagEnabled')
            ->willReturn(false);

        $this->assertFalse(
            $this->model->isAllowUpa(\Omise\Payment\Model\Config\Promptpay::CODE)
        );
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isAllowUpaReturnsFalseForUnsupportedMethod()
    {
        $this->configMock->expects($this->once())
            ->method('getIsUpaFeatureFlagEnabled')
            ->willReturn(true);

        $this->assertFalse(
            $this->model->isAllowUpa(\Omise\Payment\Model\Config\CcGooglePay::CODE)
        );
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function getMethodIdReturnsMobileBanking()
    {
        $this->assertEquals(
            'mobile_banking',
            $this->model->getMethodId('omise_offsite_mobilebanking_bay')
        );
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function getMethodIdReturnsInstallment()
    {
        $this->assertEquals(
            'installment',
            $this->model->getMethodId('omise_offsite_installment_bay')
        );
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function getMethodIdReturnsMappedId()
    {
        $this->assertEquals(
            \Omise\Payment\Model\Config\Promptpay::ID,
            $this->model->getMethodId(
                \Omise\Payment\Model\Config\Promptpay::CODE
            )
        );
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function getMethodIdReturnsNull()
    {
        $this->assertNull(
            $this->model->getMethodId('unknown_method')
        );
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @covers \Omise\Payment\Helper\OmiseHelper::__construct
     */
    public function testConstructorInitializesHelperDependencies(): void
    {
        $helper = new OmiseHelper(
            $this->configMock,
            $this->scopeConfig,
            $this->omiseCheckoutSession
        );

        $this->assertInstanceOf(OmiseHelper::class, $helper);
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @covers \Omise\Payment\Helper\OmiseHelper::checkoutSessionEndpoint
     */
    public function testCheckoutSessionEndpointComesFromConfig(): void
    {
        $this->configMock->expects($this->once())
            ->method('checkoutSessionEndpoint')
            ->willReturn('https://checkout.example/');

        $this->assertSame('https://checkout.example/', $this->model->checkoutSessionEndpoint());
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @covers \Omise\Payment\Helper\OmiseHelper::isOrderOmisePayment
     * @covers \Omise\Payment\Helper\OmiseHelper::getOrderChargeId
     */
    public function testGetOrderChargeIdReturnsExistingChargeIdWithoutLoadingCheckoutSession(): void
    {
        list($order) = $this->createOrderWithPayment('omise_upa', ['charge_id' => 'ch_direct']);
        $this->omiseCheckoutSession->expects($this->never())->method('getSessionInfo');

        $this->assertSame('ch_direct', $this->model->getOrderChargeId($order));
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @covers \Omise\Payment\Helper\OmiseHelper::isOrderOmisePayment
     * @covers \Omise\Payment\Helper\OmiseHelper::getOrderChargeId
     * @covers \Omise\Payment\Helper\OmiseHelper::getFinalPayment
     */
    public function testGetOrderChargeIdLoadsSessionAndSelectsPaymentMatchingSessionStatus(): void
    {
        list($order) = $this->createOrderWithPayment('omise_upa', ['session_id' => 'sess_123']);
        $this->configMock->expects($this->once())->method('setStoreId')->with(7);
        $session = (object) [
            'payments' => [
                ['charge_id' => 'ch_pending', 'status' => 'pending'],
                ['charge_id' => 'ch_succeeded', 'status' => 'succeeded'],
            ],
            'status' => 'SUCCEEDED',
        ];
        $this->omiseCheckoutSession->expects($this->once())
            ->method('getSessionInfo')
            ->with('sess_123')
            ->willReturn($session);

        $this->assertSame('ch_succeeded', $this->model->getOrderChargeId($order));
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @covers \Omise\Payment\Helper\OmiseHelper::isOrderOmisePayment
     * @covers \Omise\Payment\Helper\OmiseHelper::getOrderChargeId
     * @covers \Omise\Payment\Helper\OmiseHelper::getFinalPayment
     */
    public function testGetOrderChargeIdFallsBackToLastPaymentWithChargeWhenStatusesDoNotMatch(): void
    {
        list($order) = $this->createOrderWithPayment('omise_upa', ['session_id' => 'sess_456']);
        $session = (object) [
            'payments' => [
                ['charge_id' => 'ch_first'],
                ['status' => 'failed'],
                ['charge_id' => 'ch_last', 'status' => 'failed'],
            ],
            'status' => 'succeeded',
        ];
        $this->omiseCheckoutSession->expects($this->once())
            ->method('getSessionInfo')
            ->with('sess_456')
            ->willReturn($session);

        $this->assertSame('ch_last', $this->model->getOrderChargeId($order));
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @covers \Omise\Payment\Helper\OmiseHelper::isOrderOmisePayment
     * @covers \Omise\Payment\Helper\OmiseHelper::getOrderChargeId
     * @covers \Omise\Payment\Helper\OmiseHelper::getFinalPayment
     * @dataProvider emptySessionPaymentsProvider
     */
    public function testGetOrderChargeIdReturnsNullWhenSessionHasNoChargePayment($payments): void
    {
        list($order) = $this->createOrderWithPayment('omise_upa', ['session_id' => 'sess_empty']);
        $this->omiseCheckoutSession->expects($this->once())
            ->method('getSessionInfo')
            ->willReturn((object) ['payments' => $payments, 'status' => 'succeeded']);

        $this->assertNull($this->model->getOrderChargeId($order));
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @covers \Omise\Payment\Helper\OmiseHelper::isOrderOmisePayment
     * @covers \Omise\Payment\Helper\OmiseHelper::getOrderChargeId
     */
    public function testGetOrderChargeIdReturnsNullWhenCheckoutSessionCannotBeLoaded(): void
    {
        list($order) = $this->createOrderWithPayment('omise_upa', ['session_id' => 'sess_missing']);
        $this->omiseCheckoutSession->expects($this->once())
            ->method('getSessionInfo')
            ->with('sess_missing')
            ->willReturn(null);

        $this->assertNull($this->model->getOrderChargeId($order));
    }

    public function emptySessionPaymentsProvider(): array
    {
        return [
            'empty list' => [[]],
            'no charge ids' => [[['status' => 'succeeded']]],
            'not an array' => ['invalid'],
        ];
    }

    /**
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @covers \Omise\Payment\Helper\OmiseHelper::isOrderOmisePayment
     * @covers \Omise\Payment\Helper\OmiseHelper::getOrderChargeId
     */
    public function testGetOrderChargeIdIgnoresNonOmiseOrders(): void
    {
        list($order) = $this->createOrderWithPayment('other_payment', ['charge_id' => 'ch_other']);
        $this->omiseCheckoutSession->expects($this->never())->method('getSessionInfo');

        $this->assertNull($this->model->getOrderChargeId($order));
    }

    /**
     * @return array
     */
    private function createOrderWithPayment(string $methodCode, array $additionalInformation): array
    {
        $method = $this->createMock(MethodInterface::class);
        $method->method('getCode')->willReturn($methodCode);

        $payment = $this->createMock(Payment::class);
        $payment->method('getMethodInstance')->willReturn($method);
        $payment->method('getAdditionalInformation')->willReturnCallback(
            function ($key) use ($additionalInformation) {
                return $additionalInformation[$key] ?? null;
            }
        );

        $order = $this->createMock(Order::class);
        $order->method('getPayment')->willReturn($payment);
        $order->method('getStoreId')->willReturn(7);

        return [$order, $payment];
    }
}

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

    protected $scopeConfig;

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
        $this->scopeConfig = $this->createMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->deploymentConfig = $this->createMock('Magento\Framework\App\DeploymentConfig');
        $this->model = new OmiseHelper($this->configMock, $this->scopeConfig, $this->deploymentConfig);
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

        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(
                'omise_payment/omise_feature_upa',
                false
            )
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
    public function checkoutSessionEndpointReturnsCorrectUrl()
    {
        $this->assertEquals(
            'https://checkout-page.omise.co/',
            $this->model->checkoutSessionEndpoint()
        );
    }

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
}

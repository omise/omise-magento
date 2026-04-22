<?php

namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Observer\InstallmentDataAssignObserver;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Model\Config\Cc;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Block\Adminhtml\System\Config\Form\Field\Webhook;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Capability;
use Magento\Framework\UrlInterface;

class UPAPaymentDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    const AMOUNT = 'amount';

    /**
     * @var string
     */
    const CURRENCY = 'currency';

    /**
     * @var string
     */
    const DESCRIPTION = 'description';

    /**
     * @var string
     */
    const METADATA = 'metadata';

    /**
     * @var string
     */
    const ZERO_INTEREST_INSTALLMENTS = 'zero_interest_installments';

    /**
     * @var string
     */
    const WEBHOOKS_ENDPOINT = 'webhook_endpoints';

    /**
     * @var \Omise\Payment\Model\Config\Cc
     */
    private $ccConfig;

    /**
     * @var OmiseMoney
     */
    private $money;

    private $capability;

    /**
     * @var OmiseHelper
     */
    private $omiseHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Omise\Payment\Model\Config\Cc $ccConfig
     * @param Capabilities $capabilities
     * @param OmiseHelper $omiseHelper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Cc $ccConfig,
        OmiseMoney $money,
        Capability $capability,
        OmiseHelper $omiseHelper,
        UrlInterface $urlBuilder
    ) {
        $this->money = $money;
        $this->ccConfig = $ccConfig;
        $this->capability = $capability;
        $this->omiseHelper = $omiseHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {   
        $payment = SubjectReader::readPayment($buildSubject);
        $order   = $payment->getOrder();
        $method  = $payment->getPayment();
        $store_id = $order->getStoreId();
        $methodCode = $payment->getPayment()->getMethod();
        $currency = $order->getCurrencyCode();
        
        $methodCode = $this->omiseHelper->getMethodId($methodCode);
        
        $requestBody = array(
            'amount' => $this->money->setAmountAndCurrency(
                    $order->getGrandTotalAmount(),
                    $currency
                )->toSubunit(),
            'currency'        => $currency,
            'order_id'        => (string) $order->getOrderIncrementId(),
            'description'     => 'Magento Order id ' . $order->getOrderIncrementId(),
            'payment_methods' => ['installment'],
            'redirect_urls'   => array(
                'complete_url' => $this->urlBuilder->getUrl('omise/callback/upacallback'),
                'cancel_url'   => $this->urlBuilder->getUrl('omise/payment/cancel'),
            ),
            "refund_policy_link" => "https://opn.oo0/refund",
            "session_expires_at" => null,
            "expires_at" => null,
            "is_link" => true,
            "multi_charge" => true,
            "require_save_card" => true,
            "enable_passkey" => true,
            "is_upa" => true
        );
        /*$locale = substr( strtolower( get_locale() ), 0, 2 );
        if ( ! empty( $locale ) ) {
            $payload['locale'] = $locale;
        }
        $payload['locale'] = $locale;*/
        return $requestBody;
    }

    /**
     * Set zero_interest_installment to true for installment Maybank
     */
    public function enableZeroInterestInstallments($method)
    {
        $isInstallment = Installment::CODE === $method->getMethod();
        $installmentId = $method->getAdditionalInformation(InstallmentDataAssignObserver::OFFSITE);
        return $isInstallment && (Installment::MBB_ID === $installmentId);
    }
}

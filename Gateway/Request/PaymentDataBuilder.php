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

class PaymentDataBuilder implements BuilderInterface
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
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     * @param Omise\Payment\Model\Config\Cc $ccConfig
     * @param Capabilities $capabilities
     * @param OmiseHelper $omiseHelper
     */
    public function __construct(
        Cc $ccConfig,
        OmiseMoney $money,
        Capability $capability
    ) {
        $this->money = $money;
        $this->ccConfig = $ccConfig;
        $this->capability = $capability;
    }

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {   
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/omise-upa.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('***PaymentDataBuilder called');
        
        $payment = SubjectReader::readPayment($buildSubject);
        $order   = $payment->getOrder();
        $method  = $payment->getPayment();
        $store_id = $order->getStoreId();
        $methodCode = $payment->getPayment()->getMethod();
        
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $manager = $om->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store = $manager->getStore($store_id);
        $currency = $order->getCurrencyCode();

        $isUpaAllow = $this->omiseHelper->isAllowUpa($methodCode);
        $logger->info('***IS OFFSITE : '.$isUpaAllow);
        
        if($isUpaAllow){
            $logger->info('***UPA FLOW START***');
            $requestBody = array(
                'amount' => $this->money->setAmountAndCurrency(
                        $order->getGrandTotalAmount(),
                        $currency
                    )->toSubunit(),
                'currency'        => $currency,
                'order_id'        => (string) $order->getOrderIncrementId(),
                'description'     => 'Magento Order id ' . $order->getOrderIncrementId(),
                'payment_methods' => [$methodCode],
                'redirect_urls'   => array(
                    'complete_url' => "https://www.omise.co",
                    'cancel_url'   => "https://www.google.com",
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
        }else{
            $requestBody = [
                self::AMOUNT      => $this->money->setAmountAndCurrency(
                    $order->getGrandTotalAmount(),
                    $currency
                )->toSubunit(),
                self::CURRENCY    => $currency,
                self::DESCRIPTION => 'Magento 2 Order id ' . $order->getOrderIncrementId(),
                self::METADATA    => [
                    'order_id' => $order->getOrderIncrementId(),
                    'store_id' => $order->getStoreId(),
                    'store_name' => $store->getName()
                ]
            ];
        }

        if ($this->ccConfig->isDynamicWebhooksEnabled()) {
            $webhookUrl = $store->getBaseUrl() . Webhook::URI;
            $requestBody[self::WEBHOOKS_ENDPOINT] = [$webhookUrl];
        }
        // Set zero_interest_installment to true for installment Maybank only
        if ($this->enableZeroInterestInstallments($method)) {
            $requestBody[self::ZERO_INTEREST_INSTALLMENTS] = true;
        }

        if (Installment::CODE === $method->getMethod()) {
            $card = $method->getAdditionalInformation(InstallmentDataAssignObserver::CARD);
            if ($card !== null) {
                $requestBody['card'] = $card;
            }

            $source = $method->getAdditionalInformation(InstallmentDataAssignObserver::SOURCE);
            if ($source !== null) {
                $requestBody['source'] = $source;
            }
        }

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

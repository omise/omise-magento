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
use Omise\Payment\Model\Capabilities;

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

    private $capabilities;

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     * @param Omise\Payment\Model\Config\Cc $ccConfig
     */
    public function __construct(
        Cc $ccConfig,
        OmiseMoney $money,
        Capabilities $capabilities
    ) {
        $this->money = $money;
        $this->ccConfig = $ccConfig;
        $this->capabilities = $capabilities;
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
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $manager = $om->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store = $manager->getStore($store_id);
        $currency = $order->getCurrencyCode();

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

        if ($this->ccConfig->isDynamicWebhooksEnabled()) {
            $webhookUrl = $store->getBaseUrl() . Webhook::URI;
            $requestBody[self::WEBHOOKS_ENDPOINT] = [$webhookUrl];
        }

        // Set zero_interest_installment to true for installment Maybank only
        if ($this->enableZeroInterestInstallments($method)) {
            $requestBody[self::ZERO_INTEREST_INSTALLMENTS] = true;
        }

        if (Cc::CODE === $method->getMethod()) {
            $requestBody[self::METADATA]['secure_form_enabled'] = $this->ccConfig->getSecureForm();
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

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
     * @var \Omise\Payment\Model\Config\Config
     */
    private $config;

    /**
     * @var OmiseMoney
     */
    private $money;

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     * @param Omise\Payment\Model\Config\Cc $ccConfig
     */
    public function __construct(Cc $ccConfig, OmiseMoney $money, Config $config)
    {
        $this->money = $money;
        $this->ccConfig = $ccConfig;
        $this->config = $config;
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
        $store_name = $manager->getStore($store_id)->getName();
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
                'store_name' => $store_name
            ]
        ];

        if ($this->config->isWebhookEnabled()) {
            $webhookUrl = $manager->getStore()->getBaseUrl() . Webhook::URI;
            $requestBody[self::WEBHOOKS_ENDPOINT] = [$webhookUrl];
        }

        if (Installment::CODE === $method->getMethod()) {
            $requestBody[self::ZERO_INTEREST_INSTALLMENTS] = $this->isZeroInterestInstallment($method);
        }

        if (Cc::CODE === $method->getMethod()) {
            $requestBody[self::METADATA]['secure_form_enabled'] = $this->ccConfig->getSecureForm();
        }

        return $requestBody;
    }

    public function isZeroInterestInstallment($method)
    {
        $installmentId = $method->getAdditionalInformation(InstallmentDataAssignObserver::OFFSITE);
        return ('installment_mbb' === $installmentId);
    }
}

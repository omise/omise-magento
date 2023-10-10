<?php

namespace Omise\Payment\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface as MagentoScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as MagentoScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;

class Config
{
    /**
     * @var string
     */
    const CODE = 'omise';

    /**
     * @var string
     */
    const MODULE_NAME = 'Omise_Payment';

    /**
     * To fetch value from specific store. It will fetch from default is no storeId passed
     *
     * @var integer
     */
    private $storeId = null;
    private $storeLocale = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    private $canInitialize = false;

    public function __construct(MagentoScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->init();
    }

    private function init()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $localeCode =  $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $this->setStoreId($storeId);
        $this->setStoreLocale($localeCode);

        // Initialize only if both keys are present
        if ($this->getPublicKey() && $this->getSecretKey()) {
            $this->canInitialize = true;
        }
    }

    public function canInitialize()
    {
        return $this->canInitialize;
    }

    /**
     * Change the store ID from the default store to fetch store specific values
     *
     * @param  integer|null  $storeId
     * @return $this
     */
    public function setStoreId($storeId = null)
    {
        $this->storeId = $storeId;
    }

    /**
     * @return mixed
     */
    public function setStoreLocale($locale)
    {
        return $this->storeLocale = $locale;
    }

    /**
     * @return mixed
     */
    public function getStoreLocale()
    {
        return $this->storeLocale;
    }

    /**
     * @param  string $field
     * @param  string $code
     *
     * @return mixed
     */
    public function getValue($field, $code = self::CODE)
    {
        $value = $this->scopeConfig->getValue(
            'payment/' . $code . '/' . $field,
            MagentoScopeInterface::SCOPE_STORE,
            $this->storeId ?? null
        );
        return $value ? trim($value) : null;
    }

    /**
     * Check if Omise's sandbox mode enable or not
     *
     * @return bool
     */
    public function isSandboxEnabled()
    {
        if ($this->getValue('sandbox_status')) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve Omise public key whether live or test key
     *
     * @return string
     */
    public function getPublicKey()
    {
        if ($this->isSandboxEnabled()) {
            return $this->getTestPublicKey();
        }

        return $this->getLivePublicKey();
    }

    /**
     * Retrieve Omise live public key
     *
     * @return string
     */
    protected function getLivePublicKey()
    {
        return $this->getValue('live_public_key');
    }

    /**
     * Retrieve Omise test public key
     *
     * @return string
     */
    protected function getTestPublicKey()
    {
        return $this->getValue('test_public_key');
    }

    /**
     * Retrieve Omise secret key whether live or test key
     *
     * @return string
     */
    public function getSecretKey()
    {
        if ($this->isSandboxEnabled()) {
            return $this->getTestSecretKey();
        }

        return $this->getLiveSecretKey();
    }

    /**
     * Retrieve Omise live secret key
     *
     * @return string
     */
    protected function getLiveSecretKey()
    {
        return $this->getValue('live_secret_key');
    }

    /**
     * Retrieve Omise test secret key
     *
     * @return string
     */
    protected function getTestSecretKey()
    {
        return $this->getValue('test_secret_key');
    }

    /**
     * Check if using webhook or not
     *
     * @return bool
     */
    public function isWebhookEnabled()
    {
        return $this->getValue('webhook_status');
    }

    /**
     * Check if using dynamic webhooks or not
     *
     * @return bool
     */
    public function isDynamicWebhooksEnabled()
    {
        return $this->isWebhookEnabled() && $this->getValue('dynamic_webhooks');
    }

    /**
     * Retrieve the order status in which to generate invoice at
     *
     * @return string
     */
    public function getSendInvoiceAtOrderStatus()
    {
        $orderStatus = $this->getValue('generate_invoice_at_order_status');

        // Previously, our default value of 'Generate invoice at order status' was
        // '\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT'. So, this is for the
        // merchants who have already installed our module so that they don't have
        // to update the `Generate invoice at order status` setting
        if ($orderStatus === '\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT') {
            return Order::STATE_PENDING_PAYMENT;
        }

        return $orderStatus;
    }
}

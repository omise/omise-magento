<?php

namespace Omise\Payment\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface as MagentoScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as MagentoScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

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
        $this->setStoreId($storeId);

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
     * Retrieve the order status in which to generate invoice at
     *
     * @return string
     */
    public function getSendInvoiceAtOrderStatus()
    {
        return $this->getValue('generate_invoice_at_order_status');
    }
}

<?php
namespace Omise\Payment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface as MagentoScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as MagentoScopeInterface;

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(MagentoScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param  string $field
     *
     * @return mixed
     */
    public function getValue($field)
    {
        return $this->scopeConfig->getValue(
            'payment/' . self::CODE . '/' . $field,
            MagentoScopeInterface::SCOPE_STORE
        );
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
     * Check if Omise's sandbox mode enable or not
     *
     * @return bool
     */
    public function is3DSecureEnabled()
    {
        if ($this->getValue('3ds')) {
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
}

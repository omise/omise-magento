<?php
namespace Omise\Payment\Model;

use Omise\Payment\Helper\OmiseHelper;

class OmiseConfig
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
     * Omise public key
     *
     * @var string
     */
    protected $publicKey;

    /**
     * Omise secret key
     *
     * @var string
     */
    protected $secretKey;

    /**
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    protected $omiseHelper;

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     */
    public function __construct(OmiseHelper $helper)
    {
        $this->omiseHelper = $helper;
    }

    /**
     * Check if Omise's sandbox mode enable or not
     *
     * @return bool
     */
    public function isSandboxEnabled()
    {
        if ($this->omiseHelper->getConfig('sandbox_status')) {
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
        if ($this->omiseHelper->getConfig('3ds')) {
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
        return $this->omiseHelper->getConfig('live_public_key');
    }

    /**
     * Retrieve Omise test public key
     *
     * @return string
     */
    protected function getTestPublicKey()
    {
        return $this->omiseHelper->getConfig('test_public_key');
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
        return $this->omiseHelper->getConfig('live_secret_key');
    }

    /**
     * Retrieve Omise test secret key
     *
     * @return string
     */
    protected function getTestSecretKey()
    {
        return $this->omiseHelper->getConfig('test_secret_key');
    }
}
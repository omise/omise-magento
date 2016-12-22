<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Model\CcConfig;
use Omise\Payment\Helper\OmiseHelper;

class OmiseConfigProvider implements ConfigProviderInterface
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
     * @var \Magento\Payment\Model\CcConfig $ccConfig
     */
    protected $ccConfig;

    /**
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    protected $omiseHelper;

    /**
     * @param \Magento\Payment\Model\CcConfig   $ccConfig
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     */
    public function __construct(CcConfig $ccConfig, OmiseHelper $omiseHelper)
    {
        $this->ccConfig    = $ccConfig;
        $this->omiseHelper = $omiseHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'ccform' => [
                    'months' => [self::CODE => $this->getCcMonths()],
                    'years' => [self::CODE => $this->getCcYears()],
                ],
                'omise' => [
                    'publicKey' => $this->getPublicKey(),
                ],
            ]
        ];
    }

    /**
     * Retrieve list of months translation
     *
     * @return array
     */
    protected function getCcMonths()
    {
        return $this->ccConfig->getCcMonths();
    }

    /**
     * Retrieve array of available years
     *
     * @return array
     */
    protected function getCcYears()
    {
        return $this->ccConfig->getCcYears();
    }

    /**
     * Check if Omise's sandbox mode enable or not
     *
     * @return bool
     */
    protected function isSandboxEnabled()
    {
        if ($this->omiseHelper->getConfig('sandbox_status')) {
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

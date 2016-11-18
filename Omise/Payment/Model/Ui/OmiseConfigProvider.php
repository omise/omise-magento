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
     * Retrieve list of months translation
     *
     * @return array
     */
    public function getCcMonths()
    {
        return $this->ccConfig->getCcMonths();
    }

    /**
     * Retrieve array of available years
     *
     * @return array
     */
    public function getCcYears()
    {
        return $this->ccConfig->getCcYears();
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
     * Retrieve Omise live public key
     *
     * @return string
     */
    public function getLivePublicKey()
    {
        return $this->omiseHelper->getConfig('live_public_key');
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
     * Retrieve Omise test public key
     *
     * @return string
     */
    public function getTestPublicKey()
    {
        return $this->omiseHelper->getConfig('test_public_key');
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
}

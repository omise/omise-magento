<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Model\CcConfig;
use Omise\Payment\Model\OmiseConfig;

class OmiseConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Payment\Model\CcConfig $ccConfig
     */
    protected $ccConfig;

    /**
     * @var \Omise\Payment\Model\OmiseConfig
     */
    protected $omiseConfig;

    /**
     * @param \Magento\Payment\Model\CcConfig  $ccConfig
     * @param \Omise\Payment\Model\OmiseConfig $omiseConfig
     */
    public function __construct(CcConfig $ccConfig, OmiseConfig $omiseConfig)
    {
        $this->ccConfig    = $ccConfig;
        $this->omiseConfig = $omiseConfig;
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
                    'months' => [$this->omiseConfig::CODE => $this->getCcMonths()],
                    'years' => [$this->omiseConfig::CODE => $this->getCcYears()],
                ],
                $this->omiseConfig::CODE => [
                    'publicKey' => $this->omiseConfig->getPublicKey(),
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
}

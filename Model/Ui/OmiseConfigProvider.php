<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Model\CcConfig;
use Omise\Payment\Model\Config;

class OmiseConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Payment\Model\CcConfig $ccConfig
     */
    protected $ccConfig;

    /**
     * @var \Omise\Payment\Model\Config
     */
    protected $config;

    /**
     * @param \Magento\Payment\Model\CcConfig  $ccConfig
     * @param \Omise\Payment\Model\Config      $omiseConfig
     */
    public function __construct(CcConfig $ccConfig, Config $config)
    {
        $this->ccConfig = $ccConfig;
        $this->config   = $config;
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
                    'months' => [Config::CODE => $this->ccConfig->getCcMonths()],
                    'years'  => [Config::CODE => $this->ccConfig->getCcYears()],
                ],
                Config::CODE => [
                    'publicKey'       => $this->config->getPublicKey(),
                    'process3DSecure' => $this->config->getConfig('3ds')
                ],
            ]
        ];
    }
}

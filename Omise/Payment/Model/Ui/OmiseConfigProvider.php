<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Model\CcConfig;

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
     * @param \Magento\Payment\Model\CcConfig $ccConfig
     */
    public function __construct(CcConfig $ccConfig)
    {
        $this->ccConfig = $ccConfig;
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
            ]
        ];
    }
}

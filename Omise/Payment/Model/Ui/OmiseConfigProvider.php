<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Model\CcConfig;

class OmiseConfigProvider implements ConfigProviderInterface
{
    const CODE = 'omise';

    protected $ccConfig;

    public function __construct(CcConfig $ccConfig)
    {
        $this->ccConfig = $ccConfig;
    }

    public function getCcMonths()
    {
        return $this->ccConfig->getCcMonths();
    }

    public function getCcYears()
    {
        return $this->ccConfig->getCcYears();
    }

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

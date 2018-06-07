<?php
namespace Omise\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session as MagentoCustomerSession;
use Magento\Payment\Model\CcConfig as MagentoCcConfig;
use Omise\Payment\Model\Config\Cc as OmiseCcConfig;

class CcConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $magentoCustomerSession;

    /**
     * @var \Magento\Payment\Model\CcConfig
     */
    protected $magentoCcConfig;

    /**
     * @var \Omise\Payment\Model\Config\Cc
     */
    protected $omiseCcConfig;

    public function __construct(MagentoCustomerSession $magentoCustomerSession, MagentoCcConfig $magentoCcConfig, OmiseCcConfig $omiseCcConfig)
    {
        $this->magentoCustomerSession = $magentoCustomerSession;
        $this->magentoCcConfig        = $magentoCcConfig;
        $this->omiseCcConfig          = $omiseCcConfig;
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
                    'months' => [OmiseCcConfig::CODE => $this->magentoCcConfig->getCcMonths()],
                    'years'  => [OmiseCcConfig::CODE => $this->magentoCcConfig->getCcYears()],
                ],
                OmiseCcConfig::CODE => [
                    'publicKey'          => $this->omiseCcConfig->getPublicKey(),
                    'offsitePayment'     => $this->omiseCcConfig->is3DSecureEnabled(),
                    'isCustomerLoggedIn' => $this->magentoCustomerSession->getCustomerId() ? true : false
                ],
            ]
        ];
    }
}

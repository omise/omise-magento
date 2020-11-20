<?php

namespace Omise\Payment\Model\Ui;

use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Omise\Payment\Model\Config\Cc as OmiseCCConfig;

class PromoConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    private $_storeManager;

    /**
     * @var Magento\Payment\Api\PaymentMethodListInterface;
     */
    private $_paymentLists;
    private $log;

    public function __construct(
        PaymentMethodListInterface $paymentLists,
        StoreManagerInterface      $storeManager,
        \Psr\Log\LoggerInterface $log
    ) {
        $this->log             = $log;
        $this->_paymentLists   = $paymentLists;
        $this->_storeManager   = $storeManager;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product_discamt = 0;
        $objrules = $objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        $rules = $objrules->getCollection();
        $this->log->debug("num of rules".count($rules));
        
        foreach ($rules as $singleRule) {
            //$this->log->debug(print_r($tmprule, true));
            //$this->log->debug(print_r($objectManager->create('Magento\SalesRule\Model\Rule')->load($tmprule->getId()), true));
            $rule = $objectManager->create('Magento\SalesRule\Model\Rule')->load($singleRule->getId());
            $this->log->debug(print_r($rule->debug(), true));
            $conditions = $objectManager->create('Magento\SalesRule\Model\Rule')->load($singleRule->getId())->getConditionsInstance();
            if (!$conditions) {
                $this->log->debug("no conditions, they are null");
            }
            try {
                foreach ($conditions->getNewChildSelectOptions() as $child) {
                    //$this->log->debug(print_r($child, true));
                    foreach ($child as $cond) {
                        if (is_array($cond))
                            foreach ($cond as $innerCond) {
                                if ($innerCond['label']->getText() === 'Card Bin') {
                                    $this->log->debug(print_r($innerCond['label']->getArguments(), true));
                                }

                            }
                    }
                }
            } catch (\Exception $e) {
                $this->log->debug('error while getting conditions');
            }
        }

        $this->log->debug('getting cart rules');


        $listOfActivePaymentMethods = $this->_paymentLists->getActiveList($this->_storeManager->getStore()->getId());
        foreach ($listOfActivePaymentMethods as $method) {
            if ($method->getCode() === OmiseCCConfig::CODE) {
                return [
                    'rules' => 'jacek stan'
                ];
            }
        }
        return [];
    }
}

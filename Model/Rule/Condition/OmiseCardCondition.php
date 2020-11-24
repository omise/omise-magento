<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omise\Payment\Model\Rule\Condition;

/**
 * Address rule condition data model.
 */
class OmiseCardCondition extends \Magento\Rule\Model\Condition\AbstractCondition
{
    protected $checkoutSession;
    protected $log;
    private $paymentMethod; 
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Psr\Log\LoggerInterface $log,
        array $data = []
    ) {
        //$log->debug("OmiseCardCondition -> construct");
        parent::__construct($context, $data);
        $log->debug('this is data', $data);
        $this->checkoutSession = $checkoutSession;
        $this->log = $log;
        $this->paymentMethod = $paymentMethod;
    }
 
    public function loadAttributeOptions()
    {
        //$this->log->debug("load additional attributes");
        $this->setAttributeOption([
            'card_bin' => __('Card Bin')
        ]);
        return $this;
    }
 
    public function getInputType()
    {
        return 'string';  // input type for admin condition
    }
 
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $this->setData('value_select_options', array('some' => 'Some'));
        }
        return $this->getData('value_select_options');
    }
 
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $this->log->debug("this is dependency injection logger");
        $logger = new \Zend\Log\Logger();
        $logger->addWriter(new \Zend\Log\Writer\Stream(BP . '/var/log/test.log'));
        $logger->info("this is not returning:");
        $paymentData = $this->paymentMethod->getMet();
        $logger->debug(json_encode($paymentData));

        $logger->debug('validate rule bin!!!!!');
        
        if ( isset($paymentData['additional_data']) && isset($paymentData['additional_data']['omise_card_bin']) ){
            $logger->info('setting card bin, to be validated');
            $model->setData('card_bin', $paymentData['additional_data']['omise_card_bin']);
        } else {
            $logger->info('no cart rule was set');
        }


        return parent::validate($model);
    }
}

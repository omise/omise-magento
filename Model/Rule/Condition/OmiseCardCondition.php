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
 
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
    }
 
    public function loadAttributeOptions()
    {
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
            // switch ($this->getAttribute()) {
            //     case 'country_id':
            //         $options = $this->_directoryCountry->toOptionArray();
            //         break;

            //     case 'region_id':
            //         $options = $this->_directoryAllregion->toOptionArray();
            //         break;

            //     case 'shipping_method':
            //         $options = $this->_shippingAllmethods->toOptionArray();
            //         break;

            //     case 'payment_method':
            //         $options = $this->_paymentAllmethods->toOptionArray();
            //         break;

            //     default:
            //         $options = [];
            // }
            $this->setData('value_select_options', array('some' => 'Some'));
        }
        return $this->getData('value_select_options');
    }
 
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        //$cardBin = $this->_checkoutSession->getQuote()->getShippingAddress()->getCity();
        $model->setData('card_bin', 'dBin');
        return parent::validate($model);
    }
}

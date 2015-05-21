<?php
/**
* Elias Interactive
*
* @title      Magento -> Custom Payment Module for Cash On Delivery
* @category   Mage
* @package    Mage_Local
* @author     Lee Taylor / Elias Interactive -> lee [at] eliasinteractive [dot] com
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
 
class Omise_Gateway_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
{
    /**
     * unique internal payment method identifier
     * @var string [a-z0-9_]
     */
    protected $_code            = 'omise_gateway';
    protected $_formBlockType   = 'omise_gateway/form_cc';
    protected $_infoBlockType   = 'payment/info_cc';
}
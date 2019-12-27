<?php
class Omise_Gateway_Model_Payment_Offsitealipay extends Omise_Gateway_Model_Payment_SimpleOffsite_Payment
{

    /**
     * @var string
     */
    protected $_code = 'omise_offsite_alipay';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_offsitealipay';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info';

    /**
     * @var string
     */
    protected $_callbackUrl = 'omise/callback_validateoffsitealipay';

    /**
     * @var array
     */
    protected $_currencies = array('THB');

    /**
     * @var string
     */
    protected $_type = 'alipay';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway          = true;
    protected $_canReviewPayment   = true;
    protected $_isInitializeNeeded = true;
}

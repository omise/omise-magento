<?php 
class Omise_Gateway_Model_Payment_Offsitetesco extends Omise_Gateway_Model_Payment_Offline_Payment {
    /**
     * @var string
     */
    protected $_code = 'omise_offsite_tesco';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_default';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info';

    /**
     * @var string
     */
    protected $_callbackUrl = 'omise/callback_validateoffsitetesco';

    /**
     * @var array
     */
    protected $_currencies = array('THB');

    /**
     * @var string
     */
    protected $_type = 'bill_payment_tesco_lotus';

    /**
     * @var string
     */
    protected $_successUrl = 'omise/checkout_tesco';

    /**
     * @var string
     */
    protected $_emailTemplateId ='omise_gateway_email_tesco_orderconfirmation';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway          = true;
    protected $_canReviewPayment   = true;
    protected $_isInitializeNeeded = true;
}
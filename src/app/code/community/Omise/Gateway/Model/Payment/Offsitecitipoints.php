<?php


class Omise_Gateway_Model_Payment_Offsitecitipoints extends Omise_Gateway_Model_Payment_SimpleOffsite_Payment {

    /**
     * @var string
     */
    protected $_code = 'omise_offsite_citipoints';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_offsitecitipoints';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info';

    /**
     * @var string
     */
    protected $_callbackUrl = 'omise/callback_validateoffsitecitipoints';

    /**
     * @var array
     */
    protected $_currencies = array('THB');

    /**
     * @var string
     */
    protected $_type = 'points_citi';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway          = true;
    protected $_canReviewPayment   = true;
    protected $_isInitializeNeeded = true;
}
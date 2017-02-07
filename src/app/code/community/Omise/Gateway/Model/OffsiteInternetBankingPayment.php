<?php
class Omise_Gateway_Model_OffsiteInternetBankingPayment extends Omise_Gateway_Model_Payment
{
    /**
     * @var string
     */
    protected $_code = 'omise_offsite_internet_banking';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_offsiteinternetbankingpayment';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway = true;
}

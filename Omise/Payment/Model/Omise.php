<?php
namespace Omise\Payment\Model;

use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Cc;
use Magento\Quote\Api\Data\CartInterface;

class Omise extends Cc
{
    const CODE = 'omise';

    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_code = 'omise';
    protected $_isGateway = true;

    public function authorize(InfoInterface $payment, $amount)
    {
        $payment->setAdditionalInformation('payment_type', $this->getConfigData('payment_action'));
    }

    public function isAvailable(CartInterface $quote = null)
    {
        return true;
    }

    public function validate()
    {
        return $this;
    }
}

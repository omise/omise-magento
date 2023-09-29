<?php
namespace Omise\Payment\Block\Checkout\Onepage\Success;

class PromptpayAdditionalInformation extends AdditionalInformation
{
    /**
     * Adding PromptPay Payment Information
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getPaymentType() !== 'promptpay') {
            return;
        }
        $data['order_amount'] = $this->getOrderAmount();
        $data['image_code'] = $this->getPaymentAdditionalInformation('image_code');
        $data['charge_expires_at'] = $this->getChargeExpiryDate();
        $this->addData($data);
        return parent::_toHtml();
    }

    /**
     * get expiry date
     * @return string
     */
    public function getChargeExpiryDate()
    {
        // Making sure that timezone is Thailand
        date_default_timezone_set("Asia/Bangkok");
        $timestamp = strtotime($this->getPaymentAdditionalInformation('charge_expires_at'));
        return date("M d, Y h:i A", $timestamp);
    }
}

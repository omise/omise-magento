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
        $data['charge_expires_at'] = $this->getPaymentAdditionalInformation('charge_expires_at');
        $this->addData($data);
        return parent::_toHtml();
    }
}

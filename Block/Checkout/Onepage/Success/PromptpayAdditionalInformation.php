<?php
namespace Omise\Payment\Block\Checkout\Onepage\Success;

class PromptpayAdditionalInformation extends AdditionalInformation
{
    /**
     * Adding PayNow Payment Information
     * @return string
     */
    protected function _toHtml()
    {
        if($this->getPaymentType() !== 'promptpay') {
            return;
        }
        $data['order_amount'] = $this->getOrderAmount();
        $data['offline_code'] = $this->getPaymentAdditionalInformation('qr_code_encoded');
        $data['qr_data_type'] = $this->getPaymentAdditionalInformation('qr_data_type');
        $this->addData($data);
        return parent::_toHtml();
    }
}

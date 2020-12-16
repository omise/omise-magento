<?php
namespace Omise\Payment\Block\Checkout\Onepage\Success;

class TescoAdditionalInformation extends AdditionalInformation
{
    /**
     * Adding PayNow Payment Information
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getPaymentType() !== 'bill_payment_tesco_lotus') {
            return;
        }
        $data['order_amount'] = $this->getOrderAmount();
        $data['offline_code'] = $this->getPaymentAdditionalInformation('barcode');
        $this->addData($data);
        return parent::_toHtml();
    }
}

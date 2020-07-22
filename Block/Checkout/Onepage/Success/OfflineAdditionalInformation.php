<?php
namespace Omise\Payment\Block\Checkout\Onepage\Success;

class OfflineAdditionalInformation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * $var \Omise\Payment\Helper\OmiseHelper
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Omise\Payment\Helper\OmiseHelper $helper,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Adding PayNow Payment Information
     *
     * @return string
     */
    protected function _toHtml()
    {
        $paymentData = $this->getPaymentData();
        $paymentType = $this->getPaymentType();
        if ($paymentType && $this->_helper->isQRCodePayment($paymentType)) {
            $orderCurrency = $this->_checkoutSession->getLastRealOrder()->getOrderCurrency()->getCurrencyCode();
            $this->addData([
                'qrcode' => $paymentData['additional_information']['qr_code_encoded'],
                'order_amount' => number_format($paymentData['amount_ordered'], 2) .' '.$orderCurrency
            ]);
            return parent::_toHtml();
        }
    }

    /**
     * @return boolean|string
     */
    public function getPaymentType() 
    {
        $paymentData = $this->getPaymentData();
        return isset($paymentData['additional_information']['payment_type']) ? $paymentData['additional_information']['payment_type'] : false ;
    }

    public function getPaymentData() 
    {
        return $this->_checkoutSession->getLastRealOrder()->getPayment()->getData();
    }
}
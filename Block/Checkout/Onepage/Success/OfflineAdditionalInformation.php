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
     * @var string
     */
    protected $paymentType;

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
        $order       = $this->_checkoutSession->getLastRealOrder();
        $paymentData = $order->getPayment()->getData();
        $this->paymentType = isset($paymentData['additional_information']['payment_type']) ? $paymentData['additional_information']['payment_type'] : null;
        if ($this->paymentType && $this->_helper->isQRCodePayment($this->paymentType)) {
            $orderCurrency = $order->getOrderCurrency()->getCurrencyCode();
            $data['order_amount'] = number_format($paymentData['amount_ordered'], 2) .' '.$orderCurrency;
            if($this->paymentType == 'bill_payment_tesco_lotus') { 
                $data['offline_code'] = $paymentData['additional_information']['barcode'];
            } else {
                $data['offline_code'] = $paymentData['additional_information']['qr_code_encoded'];
            }
            $this->addData($data);
            return parent::_toHtml();
        }
    }
    /**
     * returns payment method type.
     * @return string
     */
    public function getPaymentType() {
        return $this->paymentType;
    }
}

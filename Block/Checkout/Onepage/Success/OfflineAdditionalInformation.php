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
     * @return string
     */
    protected function _toHtml()
    {
        $order       = $this->getOrder();
        $paymentData = $order->getPayment()->getData();
        $this->paymentType = isset($paymentData['additional_information']['payment_type']) ? $paymentData['additional_information']['payment_type'] : null;
        if ($this->paymentType && $this->_helper->isPayableByImageCode($this->paymentType)) {
            $data = $this->setPaymentData($order, $paymentData);
            return parent::_toHtml();
        }
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder() {
        return $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * returns payment method type.
     * @return string
     */
    public function getPaymentType() {
        return $this->paymentType;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param array $paymentData
     * @return void
     */
    public function setPaymentData($order, $paymentData) {
        $orderCurrency = $order->getOrderCurrency()->getCurrencyCode();
        $data['order_amount'] = number_format($paymentData['amount_ordered'], 2) .' '.$orderCurrency;
        if($this->paymentType == 'bill_payment_tesco_lotus') { 
            $data['offline_code'] = $paymentData['additional_information']['barcode'];
        } else if($this->paymentType == 'promptpay') {
            $data['offline_code'] = $paymentData['additional_information']['qr_code_encoded'];
            $data['qr_data_type'] = $paymentData['additional_information']['qr_data_type'];
        } else{
            $data['offline_code'] = $paymentData['additional_information']['qr_code_encoded'];
        }
        $this->addData($data);
    }
}

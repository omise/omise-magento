<?php

namespace Omise\Payment\Block\Checkout\Onepage\Success;

class ConveniencestoreAdditionalInformation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * Return HTML code with tesco lotus payment infromation
     *
     * @return string
     */
    protected function _toHtml()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        $paymentData = $order->getPayment()->getData();
        $paymentAdditionalInfo = $paymentData['additional_information'];

        if (!array_key_exists('payment_type', $paymentAdditionalInfo)) {
            return;
        }

        $paymentType = $paymentAdditionalInfo['payment_type'];

        if (!isset($paymentType) || $paymentType !== 'econtext') {
            return;
        }

        $orderCurrency = $order->getOrderCurrency()->getCurrencyCode();

        $this->addData([
            'link' => $paymentAdditionalInfo['charge_authorize_uri'],
            'order_amount' => number_format($paymentData['amount_ordered'], 2) .' '.$orderCurrency
        ]);
        
        return parent::_toHtml();
    }
}

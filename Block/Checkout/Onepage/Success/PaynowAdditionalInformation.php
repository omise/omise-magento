<?php
namespace Omise\Payment\Block\Checkout\Onepage\Success;

class PaynowAdditionalInformation extends \Magento\Framework\View\Element\Template
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
     * Adding PayNow Payment Information
     *
     * @return string
     */
    protected function _toHtml()
    {
        $paymentData = $this->_checkoutSession->getLastRealOrder()->getPayment()->getData();
        if (isset($paymentData['additional_information']['payment_type']) || $paymentData['additional_information']['payment_type'] === 'paynow') {
            $orderCurrency = $this->_checkoutSession->getLastRealOrder()->getOrderCurrency()->getCurrencyCode();
            $this->addData([
                'paynow_qrcode' => $paymentData['additional_information']['qr_code_encoded'],
                'order_amount' => number_format($paymentData['amount_ordered'], 2) .' '.$orderCurrency
            ]);
        }
        
        return parent::_toHtml();
    }
}

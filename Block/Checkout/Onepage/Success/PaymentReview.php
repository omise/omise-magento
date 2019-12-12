<?php

namespace Omise\Payment\Block\Checkout\Onepage\Success;


class PaymentReview extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session $_checkoutSession
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Framework\UrlInterface $_urlBuilder
     */
    protected $_urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder
        )
	{
        $this->_checkoutSession = $checkoutSession;
        $this->_urlBuilder      = $urlBuilder;

		parent::__construct($context);
	}

    public function getCheckPaymentStatusUrl() {
        $orderId = $this->_checkoutSession->getData('last_order_id');

        return $this->_urlBuilder->getBaseUrl() . "rest/V1/orders/$orderId/payment-status";
    }

    /**
    * Return HTML code with payment confirmation 
    *
    * @return string
    */
   protected function _toHtml()
   {
       $paymentData = $this->_checkoutSession->getLastRealOrder()->getPayment()->getData();
       $paymentType = isset($paymentData['additional_information']['payment_type']) ? $paymentData['additional_information']['payment_type'] : null;
       if (in_array($paymentType, [null, 'econtext', 'bill_payment_tesco_lotus'])) {
           return;
       }
       
       return parent::_toHtml();
   }
}
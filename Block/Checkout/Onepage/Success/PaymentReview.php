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

    public function getReorderUrl() {
        return;
    }
}
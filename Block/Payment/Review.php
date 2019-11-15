<?php

namespace Omise\Payment\Block\Payment;


class Review extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    private $_checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession
        )
	{
        $this->_checkoutSession = $checkoutSession;
		parent::__construct($context);
	}

	public function getLastOrderId()
	{
		return $this->_checkoutSession->getData('last_order_id');
	}
}
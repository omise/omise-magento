<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omise\Payment\Controller\Payment;

class Review extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		return $this->_pageFactory->create();
    }
}

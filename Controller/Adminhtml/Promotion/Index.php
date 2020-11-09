<?php

namespace Omise\Payment\Controller\Adminhtml\Promotion;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;      
    public function __construct(
            \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
            parent::__construct($context);
            $this->resultPageFactory = $resultPageFactory;
    } 
    public function execute()
    {
            $resultPage = $this->resultPageFactory->create();
            //$resultPage->setActiveMenu('Bss_CreateMenuBackend::menu');
            $resultPage->getConfig()->getTitle()->prepend(__('Omise Promotions'));
            return $resultPage;
    }
    protected function _isAllowed()
    {
            return $this->_authorization->isAllowed('Magento_CatalogRule::promo');
    }
}

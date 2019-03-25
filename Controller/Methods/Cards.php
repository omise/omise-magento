<?php
namespace Omise\Payment\Controller\Methods;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;

class Cards extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    private $pageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Stored Payment Methods'));

        return $resultPage;
    }
}

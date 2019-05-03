<?php
namespace Omise\Payment\Controller\Manage;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NotFoundException;

class Delete extends \Magento\Framework\App\Action\Action
{
    const WRONG_REQUEST = 1;

    const WRONG_TOKEN = 2;

    const ACTION_EXCEPTION = 3;

    /**
     * @var array
     */
    private $errorsMap = [];

    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var Omise\Payment\Model\Customer
     */
    private $customer;

    /**
     * @param Context  $context
     * @param Session  $customerSession
     * @param Customer $customer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session       $customerSession,
        \Omise\Payment\Model\Customer         $customer
    ) {
        parent::__construct($context, $customerSession);
        $this->customerSession = $customerSession;
        $this->customer        = $customer;

        $this->errorsMap = [
            self::WRONG_TOKEN      => __('No token found.'),
            self::WRONG_REQUEST    => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.'),
        ];
    }

    /**
     * Dispatch request
     *
     * @return Magento\Framework\Controller\ResultInterface|Magento\Framework\App\ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $request = $this->_request;
        if (!$request instanceof Http) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        $cardId = $this->getCardID($request);

        if ($cardId === null) {
            return $this->createErrorResponse(self::WRONG_TOKEN);
        }

        try {
            $this->customer->deleteCard($cardId);
        } catch (\Exception $e) {
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage();
    }

    /**
     * @param int $errorCode
     * @return Magento\Framework\App\ResponseInterface
     */
    private function createErrorResponse($errorCode)
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]
        );

        return $this->_redirect('omise/manage/cards');
    }

    /**
     * @return Magento\Framework\App\ResponseInterface
     */
    private function createSuccessMessage()
    {
        $this->messageManager->addSuccessMessage(
            __('Saved credit/debit card data was successfully removed')
        );

        return $this->_redirect('omise/manage/cards');
    }

    /**
     * @param Http $request
     * @return string|null
     */
    private function getCardID(Http $request)
    {
        return $request->getParam('card_id');
    }

    /**
     * Dispatch request
     *
     * @param  Magento\Framework\App\RequestInterface $request
     * @return Magento\Framework\App\ResponseInterface
     * @throws Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }

        return parent::dispatch($request);
    }
}
<?php
namespace Omise\Payment\Controller\Methods;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Vault\Model\PaymentTokenManagement;


class Delete extends \Magento\Framework\App\Action\Action
{
    const WRONG_REQUEST = 1;

    const WRONG_TOKEN = 2;

    const ACTION_EXCEPTION = 3;

    /**
     * @var array
     */
    private $errorsMap = [];
    private $customerSession;

    private $log;
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $jsonFactory
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param PaymentTokenManagement $paymentTokenManagement
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \PSR\Log\LoggerInterface $log
    ) {
        $this->log = $log;
        $this->log->debug('logging delete action');
        parent::__construct($context, $customerSession);
        $this->customerSession = $customerSession;
        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')
        ];
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
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
            $this->customer->delete();
        } catch (\Exception $e) {
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage();
    }

    /**
     * @param int $errorCode
     * @return ResponseInterface
     */
    private function createErrorResponse($errorCode)
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]
        );

        return $this->_redirect('omise/methods/cards');
    }

    /**
     * @return ResponseInterface
     */
    private function createSuccessMessage()
    {
        $this->messageManager->addSuccessMessage(
            __('Stored Payment Method was successfully removed')
        );
        return $this->_redirect('omise/methods/cards');
    }

    /**
     * @param Http $request
     * @return PaymentTokenInterface|null
     */
    private function getCardID(Http $request)
    {
        $this->log->debug('card'.$request->getParam('card_id'));
        return $request->getParam('card_id');
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
}

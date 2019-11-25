<?php
namespace Omise\Payment\Controller\Order;

class Reorder extends \Magento\Framework\App\Action\Action
{
    private $log;
    private $objectManager;
    private $_checkoutSession;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \PSR\Log\LoggerInterface $log,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->log = $log;
        $this->objectManager = $objectManager;
        $this->_checkoutSession = $checkoutSession;
    }

    public function execute()
    {
        $orderId = $this->_checkoutSession->getLastRealOrder()->getId();
        $items = $this->_checkoutSession->getLastRealOrder()->getItemsCollection();
        $resultRedirect = $this->resultRedirectFactory->create();
        /* @var $cart \Magento\Checkout\Model\Cart */
        $cart = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);

        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getUseNotice(true)) {
                    $this->messageManager->addNotice($e->getMessage());
                } else {
                    $this->messageManager->addError($e->getMessage());
                }
                return $resultRedirect->setPath('*/*/history');
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
                return $resultRedirect->setPath('checkout/cart');
            }
        }

        $this->log->debug('debug cart created and saved');
        $cart->save();

        return $resultRedirect->setPath('checkout/cart');
    }
}
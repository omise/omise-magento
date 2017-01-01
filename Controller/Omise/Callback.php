<?php
namespace Omise\Payment\Controller\Omise;

use Exception;
use InvalidArgumentException;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Callback extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    protected $order;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param Config $config
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        Session $session
    ) {
        parent::__construct($context);

        $this->session = $session;
    }

    // public \Magento\Sales\Model\Order
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $order          = $this->session->getLastRealOrder();

        if ($order->getId()) {
            try {
                /**
                 * 1. Get additionalData from order 'charge_id'
                 * 2. Retrieve from OmiseCharge::retrieve('id');
                 * 3. Check if that charge match with an ordr.
                 * 4. Check if charge.capture = true
                 *     4.1. Check charge.authorized = true.
                 *     4.2. Check charge.paid = true.
                 *     4.3. Update order status to something like "order paid" 
                 *     4.4. Redirect to success page.
                 *     4.5. If 4.1 or 4.2 are false, redirect to failed page with message "failed 3-D Secure validate".
                 * 5. Check if charge.capture = false
                 *     5.1. Check charge.authorized = true.
                 *     5.2. Redirect to success page.
                 *     5.3. If 5.1 is false, redirect to failed page with message "failed 3-D Secure validate".
                 * 6. throw error "process wrong, please contact admin".
                 */
                exit;
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            }
        }

        $this->messageManager->addErrorMessage(__('Cannot process 3-D Secure validation, record not found. Please check your order or contact administrator.'));

        return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
    }
}

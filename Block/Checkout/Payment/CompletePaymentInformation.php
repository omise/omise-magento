<?php
namespace Omise\Payment\Block\Checkout\Payment;

class CompletePaymentInformation extends \Omise\Payment\Block\Checkout\Onepage\Success\AdditionalInformation
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $_order;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\Data\OrderInterface $order,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_request = $request;
        $this->_order = $order;
        parent::__construct($context, $checkoutSession, $data);
    }

    /**
     * @inheritDoc
     */
    public function getOrder() {
        return $this->_order->loadByIncrementId($this->_request->getParam('orderId'));
    }

    /**
     * Adding PayNow Payment Information
     * @return string
     */
    protected function _toHtml()
    {
        $this->order = $this->getOrder();
        $data['order_amount'] = $this->getOrderAmount();
        $data['image_code'] = $this->getPaymentAdditionalInformation('image_code');
        $this->addData($data);
        return parent::_toHtml();
    }
}

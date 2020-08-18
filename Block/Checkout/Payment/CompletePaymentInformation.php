<?php
namespace Omise\Payment\Block\Checkout\Payment;

class CompletePaymentInformation extends \Omise\Payment\Block\Checkout\Onepage\Success\AdditionalInformation
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * $var \Omise\Payment\Helper\OmiseHelper
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $_order;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Omise\Payment\Helper\OmiseHelper $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_order = $order;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $checkoutSession, $helper, $data);
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
        $data['image_code'] = $this->getPaymentAdditionalInformation('qr_code_encoded');
        $this->addData($data);
        return parent::_toHtml();
    }
}

<?php
namespace Omise\Payment\Block\Checkout\Onepage\Success;

class AdditionalInformation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var string
     */
    protected $paymentType;

    /**
     * @var string
     */
    protected $paymentData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->order            = $this->getOrder();
        $this->paymentData      = $this->order->getPayment()->getData();
        $this->paymentType      = $this->getPaymentAdditionalInformation('payment_type');
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * returns payment method type.
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }
    /**
     * returns payment additional information depending on $key.
     * @param string $key
     * @return string|null
     */
    protected function getPaymentAdditionalInformation($key)
    {
        return isset($this->paymentData['additional_information'][$key])
            ? $this->paymentData['additional_information'][$key]
            : null;
    }

    /**
     * returns order amount along with order currency
     * @return string
     */
    protected function getOrderAmount()
    {
        return number_format($this->paymentData['amount_ordered'], 2)
            .' '.$this->order->getOrderCurrency()->getCurrencyCode();
    }
}

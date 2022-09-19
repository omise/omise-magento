<?php
namespace Omise\Payment\Block\Checkout\Onepage\Success;

class ConveniencestoreAdditionalInformation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

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
        parent::__construct($context, $data);
    }

    /**
     * Return HTML code with tesco lotus payment infromation
     *
     * @return string
     */
    protected function _toHtml()
    {
        $paymentData = $this->_checkoutSession->getLastRealOrder()->getPayment()->getData();

        if (
            !isset($paymentData['additional_information']['payment_type']) ||
            $paymentData['additional_information']['payment_type'] !== 'econtext'
        ) {
            return;
        }

        $orderCurrency = $this->_checkoutSession->getLastRealOrder()->getOrderCurrency()->getCurrencyCode();

        $this->addData([
            'link' => $paymentData['additional_information']['charge_authorize_uri'],
            'order_amount' => number_format($paymentData['amount_ordered'], 2) .' '.$orderCurrency
        ]);
        
        return parent::_toHtml();
    }
}

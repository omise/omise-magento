<?php
namespace Omise\Payment\Block\Checkout\Onepage\Success;

class PaynowAdditionalInformation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    private $log;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \PSR\Log\LoggerInterface $log,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
        $this->log = $log;
    }

    /**
     * paynow lotus payment infromation
     *
     * @return string
     */
    protected function _toHtml()
    {
        $paymentData = $this->_checkoutSession->getLastRealOrder()->getPayment()->getData();
        if (!isset($paymentData['additional_information']['payment_type']) || $paymentData['additional_information']['payment_type'] !== 'paynow') {
            //return;
        }
        $orderCurrency = $this->_checkoutSession->getLastRealOrder()->getOrderCurrency()->getCurrencyCode();


        $this->log->debug('from payment', ['log'=>$paymentData]);

        $this->addData([
            'paynow_barcode' => $paymentData['additional_information']['barcode'],
            'order_amount' => number_format($paymentData['amount_ordered'], 2) .' '.$orderCurrency
        ]);
        
        return parent::_toHtml();
    }
}

<?php
namespace Omise\Payment\Block\Checkout\Onepage\Success;

class TescoAdditionalInformation extends \Magento\Framework\View\Element\Template
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
     * Return tesco lotus payment infromation
     *
     * @return string
     */
    protected function _toHtml()
    {
        $paymentData = $this->_checkoutSession->getLastRealOrder()->getPayment()->getData();
        if ($paymentData['additional_information']['payment_type'] !== 'bill_payment_tesco_lotus') {
            return '';
        }

        $tescoCodeImageUrl =  $paymentData['additional_information']['barcode'];

        if (!$tescoCodeImageUrl) {
            return '';
        }
        $this->addData(
            [
                'tesco_code_url' => $tescoCodeImageUrl,
            ]
        );
        return parent::_toHtml();

    }
}

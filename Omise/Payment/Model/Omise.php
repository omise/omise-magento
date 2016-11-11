<?php
namespace Omise\Payment\Model;

use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Cc;
use Magento\Quote\Api\Data\CartInterface;

class Omise extends Cc
{
    const CODE = 'omise';

    /**
     * @var string
     */
    protected $_code = 'omise';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Authorize payment abstract method
     *
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param  \Magento\Framework\DataObject|InfoInterface $payment
     * @param  float $amount
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return $this
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The authorize action is not available.'));
        }

        $payment->setAdditionalInformation('payment_type', $this->getConfigData('payment_action'));

        return $this;
    }

    public function isAvailable(CartInterface $quote = null)
    {
        return true;
    }

    public function validate()
    {
        return $this;
    }
}

<?php

namespace Omise\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Model\Config\Atome;
use Magento\Framework\Exception\LocalizedException;
use Omise\Payment\Observer\AtomeDataAssignObserver;

class APMRequestValidator implements BuilderInterface
{
    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $order = $paymentDataObject->getOrder();
        $paymentInfo = $paymentDataObject->getPayment();
        $subTotal = $paymentInfo->getOrder()->getSubTotal();

        switch ($paymentInfo->getMethod()) {
            case Atome::CODE:
                $this->validateAtomePhoneNumber($paymentInfo);
                $this->validateAtomeAmount($order, $subTotal);
                break;
            default:
                break;
        }

        return $buildSubject;
    }

    /**
     * validate atome phone number
     *
     * @param $order
     * @param $info
     *
     * @return void
     */
    private function validateAtomePhoneNumber($info)
    {
        $number = $info->getAdditionalInformation(AtomeDataAssignObserver::PHONE_NUMBER);
        $phonePattern = "/(\+)?([0-9]{10,13})/";
        if (empty($number)) {
            throw new LocalizedException(__('Required Atome phone number'));
        }
        if (!preg_match($phonePattern, $number)) {
            throw new LocalizedException(__('Phone number should be a number in Atome'));
        }
    }

    /**
     * validate atome payment
     *
     * @param $currency
     * @param $amount
     *
     * @return void
     */
    private function validateAtomeAmount($order, $subTotal)
    {
        $limits = [
            'THB' => [
                'min' => 20,
                'max' => 150000,
            ],
            'SGD' => [
                'min' => 1.5,
                'max' => 20000,
            ],
            'MYR' => [
                'min' => 10,
                'max' => 100000,
            ]
        ];

        $currency = strtoupper($order->getCurrencyCode());
        $amount = $order->getGrandTotalAmount();

        if($subTotal === 0.0) {
            throw new LocalizedException(__('Complimentary products cannot be billed'));
        }

        if (!isset($limits[$currency])) {
            throw new LocalizedException(__('Currency not supported'));
        }

        $limit = $limits[$currency];

        if ($amount < $limit['min']) {
            throw new LocalizedException(__(
                'Amount must be greater than %1 %2',
                number_format($limit['min'], 2),
                $currency
            ));
        }

        if ($amount > $limit['max']) {
            throw new LocalizedException(__(
                'Amount must be less than %1 %2',
                number_format($limit['max'], 2),
                $currency
            ));
        }
    }
}

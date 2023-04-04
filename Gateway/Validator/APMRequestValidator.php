<?php

namespace Omise\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Model\Config\Atome;
use Magento\Framework\Exception\LocalizedException;

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

        switch ($paymentInfo->getMethod()) {
            case Atome::CODE:
                $this->validateAtomeAmount($order);
                break;
            default:
                break;
        }

        return $buildSubject;
    }

    /**
     * validate atome payment
     *
     * @param $currency
     * @param $amount
     *
     * @return void
     */
    private function validateAtomeAmount($order)
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

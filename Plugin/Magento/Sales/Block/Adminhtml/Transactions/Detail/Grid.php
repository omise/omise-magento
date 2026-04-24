<?php

namespace Omise\Payment\Plugin\Magento\Sales\Block\Adminhtml\Transactions\Detail;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Omise\Payment\Model\Config\Config;

class Grid
{
    /**
     * @param \Magento\Sales\Block\Adminhtml\Transactions\Detail\Grid $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetTransactionAdditionalInfo(
        \Magento\Sales\Block\Adminhtml\Transactions\Detail\Grid $subject,
        $result
    ) {
        // due to how it's saved on earlier version, transactionAdditionalInfo is array cast of \Magento\Sales\Api\Data\OrderPaymentInterface object
        // which causing the grid is unable to render properly, due to nested array of data (refer to parent file)
        // this is to accommodate proper rendering for data saved using earlier module versions

        // if no data is being saved
        if (count($result) === 0) {
            return $result;
        }

        $paymentAdditionalInfo = $this->getOmisePaymentInfo($result);

        // if $paymentAdditionalInfo is not following structure of \Magento\Sales\Api\Data\OrderPaymentInterface
        if ($paymentAdditionalInfo === false ||
            ($paymentAdditionalInfo && !isset($paymentAdditionalInfo[OrderPaymentInterface::METHOD]))
        ) {
             return $result;
        }

        // if method is not part of omise ecosystem
        if (stristr($paymentAdditionalInfo[OrderPaymentInterface::METHOD], Config::CODE) === false) {
            return $result;
        }

        // return additionalInformation from \Magento\Sales\Api\Data\OrderPaymentInterface
        return $paymentAdditionalInfo[OrderPaymentInterface::ADDITIONAL_INFORMATION];
    }

    /**
     * @param array $paymentInfo
     *
     * @return array|bool
     */
    protected function getOmisePaymentInfo(
        $paymentInfo
    ) {
        // saved array keys are non unicode (there is encapsulated non unicode character around `*`)
        // so it is not possible to get array value by array key (`*_data`)
        // therefore, we will check all keys thru loop with array key ended with `_data`

        $arrayKeyForData       = '_data';
        $arrayKeyForDataStrlen = strlen($arrayKeyForData);

        $foundArrayKey = '';
        foreach (array_keys($paymentInfo) as $paymentInfoKey) {
            if (substr_compare($paymentInfoKey, $arrayKeyForData, -$arrayKeyForDataStrlen) === 0) {
                $foundArrayKey = $paymentInfoKey;
                break; // stop
            }
        }

        return ($foundArrayKey === '' ? false : $paymentInfo[$foundArrayKey]);
    }
}

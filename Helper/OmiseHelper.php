<?php
namespace Omise\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class OmiseHelper extends AbstractHelper
{
    /**
     * @param  string $fieldId
     *
     * @return string
     */
    public function getConfig($fieldId)
    {
        $path = 'payment/omise/' . $fieldId;

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param  string  $currency
     * @param  integer $amount
     *
     * @return string
     */
    public function omiseAmountFormat($currency, $amount)
    {
        switch (strtoupper($currency)) {
            case 'THB':
                // Convert to satang unit
                $amount = $amount * 100;
                break;

            case 'JPY':
                break;

            default:
                break;
        }

        return $amount;
    }
}

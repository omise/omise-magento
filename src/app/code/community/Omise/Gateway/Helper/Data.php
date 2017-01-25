<?php
class Omise_Gateway_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function formatPrice($currency, $amount)
    {
        switch (strtoupper($currency)) {
            case 'THB':
                $amount = "฿" . number_format(($amount / 100), 2);
                if (preg_match('/\.00$/', $amount)) {
                    $amount = substr($amount, 0, -3);
                }
                break;

            case 'IDR':
                $amount = "Rp" . number_format(($amount / 100), 2);
                if (preg_match('/\.00$/', $amount)) {
                    $amount = substr($amount, 0, -3);
                }
                break;

            case 'SGD':
                $amount = "S$" . number_format(($amount / 100), 2);
                if (preg_match('/\.00$/', $amount)) {
                    $amount = substr($amount, 0, -3);
                }
                break;

            case 'JPY':
                $amount = number_format($amount) . "円";
                break;
        }

        return $amount;
    }
}

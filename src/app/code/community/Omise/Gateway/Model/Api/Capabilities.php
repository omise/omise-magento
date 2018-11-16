<?php
class Omise_Gateway_Model_Api_Capabilities extends Omise_Gateway_Model_Api_Object
{

    static protected $_capabilities;

    public function __construct()
    {
        if (!self::$_capabilities) self::$_capabilities = self::getCapabilities();
    }

    public function getBackends($type = '', $currency = '', $amount = null)
    {
        $params = [];
        if ($type) $params[] = self::$_capabilities->backendTypeIs($type);
        if ($currency) $params[] = self::$_capabilities->backendSupportsCurrency($currency);
        if (!is_null($amount)) $params[] = self::$_capabilities->backendSupportsChargeAmount($amount);

        return call_user_func_array([self::$_capabilities, 'getBackends'], $params);
    }

    public static function getCapabilities()
    {
        return OmiseCapabilities::retrieve();
    }

}

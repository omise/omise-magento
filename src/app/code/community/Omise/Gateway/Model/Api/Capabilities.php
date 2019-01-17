<?php
class Omise_Gateway_Model_Api_Capabilities extends Omise_Gateway_Model_Api_Object {

    static protected $_capabilities;

    /**
     * Retrieves and stores capabilities if we haven't already done so
     */
    public function __construct() {
        if (!self::$_capabilities) self::$_capabilities = self::getCapabilities();
    }

    /**
     * Retrieves details of payment backends from capabilities
     *
     * @return string
     */
    public function getBackends($type = '', $currency = '', $amount = null) {
        $params = [];
        if ($type) $params[] = self::$_capabilities->backendFilter['type']($type);
        if ($currency) $params[] = self::$_capabilities->backendFilter['currency']($currency);
        if (!is_null($amount)) $params[] = self::$_capabilities->backendFilter['chargeAmount']($amount);

        return self::$_capabilities->getBackends($params);
    }

    /**
     * Retrieves capabilities object
     *
     * @return string
     */
    public static function getCapabilities() {
        return OmiseCapabilities::retrieve();
    }

}

<?php

namespace Omise\Payment\Test\Mock;

interface RequestInterfaceMock extends \Magento\Framework\App\RequestInterface {
    public function getServer($name = null, $default = null);
}
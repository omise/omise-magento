<?php

namespace Omise\Payment\Test\Mock;

interface RequestMockInterface extends \Magento\Framework\App\RequestInterface
{
    public function getServer($name = null, $default = null);
}

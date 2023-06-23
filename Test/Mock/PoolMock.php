<?php

namespace Omise\Payment\Test\Mock;

use Magento\Framework\App\Cache\Frontend\Pool;

class PoolMock extends Pool
{
    public function getBackend()
    {
        return $this;
    }

    public function clean() {}

    public function current() {}
    
    public function next() {}
    
    public function key() {}
    
    public function valid() {}

    public function rewind() {}
}
<?php

namespace Omise\Payment\Test\Unit\Model\Api;

/**
 * Mock class
 */
class MockObject extends Object
{
    public function execute($fake_data = null)
    {
        $this->refresh($fake_data);

        return $this;
    }

    public function reload()
    {
        $this->refresh();

        return $this;
    }
}

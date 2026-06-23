<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use Omise\Payment\Model\Api\BaseObject;

/**
 * Mock class
 */
class MockObject extends BaseObject
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

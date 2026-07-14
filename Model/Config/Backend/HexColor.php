<?php

namespace Omise\Payment\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class HexColor extends Value
{
    /**
     * Validate hex color before saving.
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string)$this->getValue());

        // Allow blank value.
        // Remove this block if you want the field to be mandatory.
        if ($value === '') {
            return parent::beforeSave();
        }

        if (!preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value)) {
            throw new LocalizedException(
                __('Please enter a valid HEX color (Example: #1979C3).')
            );
        }

        return parent::beforeSave();
    }
}
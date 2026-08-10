<?php

declare(strict_types=1);

namespace Omise\Payment\Plugin\Structure\Element;

use Magento\Config\Model\Config\Structure\Element\Field as ConfigField;
use Omise\Payment\Helper\OmiseHelper;

class Field
{
    private const UPA_FIELD_IDS = [
        'is_upa_feature_flag_enabled',
        'upa_theme_color',
        'upa_text_color',
    ];

    /**
     * @param OmiseHelper $omiseHelper
     */
    public function __construct(
        OmiseHelper $omiseHelper
    ) {
        $this->omiseHelper = $omiseHelper;
    }

    /**
     * Hide merchant-specific configuration fields
     * when the deployment feature flag is disabled.
     */
    public function afterIsVisible(
        ConfigField $subject,
        bool $result
    ): bool {
        // Preserve Magento's original visibility rules.
        if (!$result) {
            return false;
        }

        $fieldId = (string) $subject->getId();

        // Apply custom visibility only to UPA fields.
        if (!in_array($fieldId, self::UPA_FIELD_IDS, true)) {
            return true;
        }

        return $this->omiseHelper->isUpaFeatureEnabled()? true : false;
    }
}

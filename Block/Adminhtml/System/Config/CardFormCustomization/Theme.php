<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization;

class Theme
{
    const DARK_COLOR = '#212121';

    public function getFormDesign($theme, $customDesign)
    {
        if (!empty($customDesign)) {
            $result = json_decode($customDesign, true);
            if (!$result) {
                return $this->getDefaultFormDesign($theme);
            }
            return $result;
        }
        return $this->getDefaultFormDesign($theme);
    }

    public function getDefaultFormDesign($theme)
    {
        return (empty($theme) || $theme == 'light')
            ? $this->getLightTheme()
            : $this->getDarkTheme();
    }

    public function getLightTheme()
    {
        return [
            'font' => [
                'name' => 'Poppins',
                'size' => 16,
            ],
            'input' => [
                'height' => '44px',
                'border_radius' => '4px',
                'border_color' => '#ced3de',
                'active_border_color' => '#1451cc',
                'background_color' => '#ffffff',
                'label_color' => self::DARK_COLOR,
                'text_color' => self::DARK_COLOR,
                'placeholder_color' => '#98a1b2',
            ],
            'checkbox' => [
                'text_color' => '#1c2433',
                'theme_color' => '#1451cc',
            ]
        ];
    }

    public function getDarkTheme()
    {
        return [
            'font' => [
                'name' => 'Poppins',
                'size' => 16,
            ],
            'input' => [
                'height' => '44px',
                'border_radius' => '4px',
                'border_color' => '#475266',
                'active_border_color' => '#475266',
                'background_color' => '#131926',
                'label_color' => '#E6EAF2',
                'text_color' => self::DARK_COLOR,
                'placeholder_color' => '#DBDBDB',
            ],
            'checkbox' => [
                'text_color' => '#E6EAF2',
                'theme_color' => '#1451CC',
            ]
        ];
    }
}

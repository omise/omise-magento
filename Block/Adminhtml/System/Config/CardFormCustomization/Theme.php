<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization;


class Theme
{

	public static function getCustomizationDesign($theme, $customDesign)
	{
		if (!empty($customDesign)) {
			return json_decode($customDesign, true);
		}
		return (empty($theme) || $theme == 'light')
			? Theme::getLightTheme()
			: Theme::getDarkTheme();
	}

	public static function getLightTheme()
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
				'label_color' => '#212121',
				'text_color' => '#212121',
				'placeholder_color' => '#98a1b2',
			],
			'checkbox' => [
				'text_color' => '#1c2433',
				'theme_color' => '#1451cc',
			]
		];
	}

	public static function getDarkTheme()
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
				'text_color' => '#212121',
				'placeholder_color' => '#DBDBDB',
			],
			'checkbox' => [
				'text_color' => '#E6EAF2',
				'theme_color' => '#1451CC',
			]
		];
	}
}

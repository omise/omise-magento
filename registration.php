<?php
use \Magento\Framework\Component\ComponentRegistrar;

require_once dirname(__FILE__) . '/Gateway/Http/Lib/omise-php/lib/Omise.php';

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Omise_Payment', __DIR__);

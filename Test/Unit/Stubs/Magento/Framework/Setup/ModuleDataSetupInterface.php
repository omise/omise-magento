<?php

namespace Magento\Framework\Setup;

interface ModuleDataSetupInterface
{
    public function startSetup();
    public function endSetup();
    public function getConnection();
    public function getTable($name);
}

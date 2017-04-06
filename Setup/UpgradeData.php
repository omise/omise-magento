<?php
namespace Omise\Payment\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @param  ModuleDataSetupInterface $setup
     * @param  ModuleContextInterface   $context
     *
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $table = $setup->getTable('core_config_data');

            $setup->getConnection()->update(
                $table,
                ['path' => 'payment/omise_cc/active'],
                ['path = ?' => 'payment/omise/active']
            );

            $setup->getConnection()->update(
                $table,
                ['path' => 'payment/omise_cc/title'],
                ['path = ?' => 'payment/omise/title']
            );

            $setup->getConnection()->update(
                $table,
                ['path' => 'payment/omise_cc/3ds'],
                ['path = ?' => 'payment/omise/3ds']
            );

            $setup->getConnection()->update(
                $table,
                ['path' => 'payment/omise_cc/payment_action'],
                ['path = ?' => 'payment/omise/payment_action']
            );
        }

        $setup->endSetup();
    }
}

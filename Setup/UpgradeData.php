<?php
namespace Omise\Payment\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute
     */
    private $attributeResource;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory              $eavSetupFactory,
        \Magento\Eav\Model\Config                       $eavConfig,
        \Magento\Customer\Model\ResourceModel\Attribute $attributeResource
    ) {
        $this->eavSetupFactory   = $eavSetupFactory;
        $this->eavConfig         = $eavConfig;
        $this->attributeResource = $attributeResource;
    }

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

        if (version_compare($context->getVersion(), '2.4.0', '<')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                Customer::ENTITY,
                'omise_customer_id',
                [
                    'type'         => 'varchar',
                    'label'        => 'Omise Customer ID',
                    'input'        => 'text',
                    'required'     => false,
                    'visible'      => true,
                    'user_defined' => false,
                    'position'     => 0,
                ]
            );

            $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'omise_customer_id');
            $attribute->setData('used_in_forms', ['adminhtml_customer']);
            $this->attributeResource->save($attribute);
        }

        $setup->endSetup();
    }
}

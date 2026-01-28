<?php

declare(strict_types=1);

namespace Magento\Eav\Setup {
    class EavSetupFactory {
        public function create($args)
        {

        }
    }

    class EavSetup {
        public function addAttribute($entity, $code, $options)
        {

        }
    }
}

namespace Magento\Eav\Model {
    class Config {
        public function getAttribute($entity, $code)
        {

        }
    }
}

namespace Magento\Eav\Model\Entity\Attribute {
    abstract class AbstractAttribute {
        abstract public function setData($key, $value);
    }
}

namespace Magento\Customer\Model\ResourceModel {
    class Attribute {
        public function save($attribute)
        {
            
        }
    }
}

namespace Magento\Framework\Setup {
    interface ModuleDataSetupInterface {
        public function startSetup();
        public function endSetup();
        public function getConnection();
        public function getTable($name);
    }

    interface ModuleContextInterface {
        public function getVersion();
    }
}

namespace Magento\Framework\DB {
    interface AdapterInterface {
        public function update($table, $data, $where);
    }
}

namespace Omise\Payment\Test\Unit\Setup {

    use Omise\Payment\Setup\UpgradeData;
    use PHPUnit\Framework\TestCase;

    use Magento\Eav\Setup\EavSetupFactory;
    use Magento\Eav\Setup\EavSetup;
    use Magento\Eav\Model\Config as EavConfig;
    use Magento\Customer\Model\ResourceModel\Attribute as AttributeResource;
    use Magento\Framework\Setup\ModuleDataSetupInterface;
    use Magento\Framework\Setup\ModuleContextInterface;
    use Magento\Framework\DB\AdapterInterface;
    use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

    class UpgradeDataTest extends TestCase
    {
        private $eavSetupFactory;
        private $eavConfig;
        private $attributeResource;
        private $upgradeData;

        protected function setUp(): void
        {
            $this->eavSetupFactory = $this->getMockBuilder(EavSetupFactory::class)
                ->onlyMethods(['create'])
                ->getMock();

            $this->eavConfig = $this->getMockBuilder(EavConfig::class)
                ->getMock();

            $this->attributeResource = $this->getMockBuilder(AttributeResource::class)
                ->getMock();

            $this->upgradeData = new UpgradeData(
                $this->eavSetupFactory,
                $this->eavConfig,
                $this->attributeResource
            );
        }

        /**
         * @covers \Omise\Payment\Setup\UpgradeData::__construct
         * @covers \Omise\Payment\Setup\UpgradeData::upgrade
         */
        public function testUpgradeVersionBelow210(): void
        {
            $setupMock = $this->getMockBuilder(ModuleDataSetupInterface::class)->getMock();
            $contextMock = $this->getMockBuilder(ModuleContextInterface::class)->getMock();
            $contextMock->method('getVersion')->willReturn('2.0.0');

            $connectionMock = $this->getMockBuilder(AdapterInterface::class)->getMock();
            $setupMock->method('getConnection')->willReturn($connectionMock);
            $setupMock->method('getTable')->willReturn('core_config_data');

            // Mock EavSetupFactory->create() and EavSetup
            $eavSetupMock = $this->getMockBuilder(EavSetup::class)
                ->onlyMethods(['addAttribute'])
                ->getMock();
            $this->eavSetupFactory->method('create')
                ->with(['setup' => $setupMock])
                ->willReturn($eavSetupMock);
            $eavSetupMock->expects($this->once())->method('addAttribute');

            // Mock attribute for setData()
            $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
                ->onlyMethods(['setData'])
                ->getMockForAbstractClass();
            $this->eavConfig->method('getAttribute')
                ->with('customer', 'omise_customer_id')
                ->willReturn($attributeMock);

            $attributeMock->expects($this->once())
                ->method('setData')
                ->with('used_in_forms', ['adminhtml_customer']);

            $this->attributeResource->expects($this->once())
                ->method('save')
                ->with($attributeMock);

            $setupMock->expects($this->once())->method('startSetup');
            $setupMock->expects($this->once())->method('endSetup');

            $connectionMock->expects($this->exactly(4))
                ->method('update')
                ->withConsecutive(
                    [
                        'core_config_data',
                        ['path' => 'payment/omise_cc/active'],
                        ['path = ?' => 'payment/omise/active']
                    ],
                    [
                        'core_config_data',
                        ['path' => 'payment/omise_cc/title'],
                        ['path = ?' => 'payment/omise/title']
                    ],
                    [
                        'core_config_data',
                        ['path' => 'payment/omise_cc/3ds'],
                        ['path = ?' => 'payment/omise/3ds']
                    ],
                    [
                        'core_config_data',
                        ['path' => 'payment/omise_cc/payment_action'],
                        ['path = ?' => 'payment/omise/payment_action']
                    ]
                );

            $this->upgradeData->upgrade($setupMock, $contextMock);
        }

        /**
         * @covers \Omise\Payment\Setup\UpgradeData::__construct
         * @covers \Omise\Payment\Setup\UpgradeData::upgrade
         */
        public function testUpgradeVersionBelow240(): void
        {
            $setupMock = $this->getMockBuilder(ModuleDataSetupInterface::class)->getMock();
            $contextMock = $this->getMockBuilder(ModuleContextInterface::class)->getMock();
            $contextMock->method('getVersion')->willReturn('2.3.0');

            $eavSetupMock = $this->getMockBuilder(EavSetup::class)
                ->onlyMethods(['addAttribute'])
                ->getMock();

            $this->eavSetupFactory->method('create')
                ->with(['setup' => $setupMock])
                ->willReturn($eavSetupMock);

            $eavSetupMock->expects($this->once())
                ->method('addAttribute')
                ->with(
                    'customer',
                    'omise_customer_id',
                    $this->arrayHasKey('type')
                );

            $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
                ->onlyMethods(['setData'])
                ->getMockForAbstractClass();

            $this->eavConfig->method('getAttribute')
                ->with('customer', 'omise_customer_id')
                ->willReturn($attributeMock);

            $attributeMock->expects($this->once())
                ->method('setData')
                ->with('used_in_forms', ['adminhtml_customer']);

            $this->attributeResource->expects($this->once())
                ->method('save')
                ->with($attributeMock);

            $setupMock->expects($this->once())->method('startSetup');
            $setupMock->expects($this->once())->method('endSetup');

            $this->upgradeData->upgrade($setupMock, $contextMock);
        }

        /**
         * @covers \Omise\Payment\Setup\UpgradeData::__construct
         * @covers \Omise\Payment\Setup\UpgradeData::upgrade
         */
        public function testUpgradeVersionAbove240(): void
        {
            $setupMock = $this->getMockBuilder(ModuleDataSetupInterface::class)->getMock();
            $contextMock = $this->getMockBuilder(ModuleContextInterface::class)->getMock();
            $contextMock->method('getVersion')->willReturn('2.5.0');

            $setupMock->expects($this->once())->method('startSetup');
            $setupMock->expects($this->once())->method('endSetup');

            // Factory mock returns a dummy EavSetup to satisfy type hints
            $this->eavSetupFactory->method('create')
                ->willReturn($this->getMockBuilder(EavSetup::class)->getMock());

            $this->upgradeData->upgrade($setupMock, $contextMock);
        }
    }
}

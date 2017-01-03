<?php
namespace Omise\Payment\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as MagentoHelperObjectManager;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\OmiseConfig;

class OmiseConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    protected $helperGetConfigAlwaysReturnTrue;

    /**
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    protected $helperGetConfigAlwaysReturnFalse;

    /**
     * @var \Omise\Payment\Model\OmiseConfig
     */
    protected $omiseConfig;

    protected function setUp()
    {
        // Mock \Omise\Payment\Helper\OmiseHelper
        $helperGetConfigAlwaysReturnTrue = $this->getMock(OmiseHelper::class, [], [], '', false);
        $helperGetConfigAlwaysReturnTrue->expects($this->any())
            ->method('getConfig')
            ->willReturn(true);

        $this->helperGetConfigAlwaysReturnTrue = $helperGetConfigAlwaysReturnTrue;

        // Mock \Omise\Payment\Helper\OmiseHelper
        $helperGetConfigAlwaysReturnFalse = $this->getMock(OmiseHelper::class, [], [], '', false);
        $helperGetConfigAlwaysReturnFalse->expects($this->any())
            ->method('getConfig')
            ->willReturn(false);

        $this->helperGetConfigAlwaysReturnFalse = $helperGetConfigAlwaysReturnFalse;
    }

    public function testIsSandboxEnabled_configIsEnabled_mustReturnTrue()
    {
        $objectManager = new MagentoHelperObjectManager($this);
        $omiseConfig   = $objectManager->getObject(OmiseConfig::class, [ 'helper' => $this->helperGetConfigAlwaysReturnTrue ]);

        $this->assertTrue($omiseConfig->isSandboxEnabled());
    }

    public function testIsSandboxEnabled_configIsDisabled_mustReturnFalse()
    {
        $objectManager = new MagentoHelperObjectManager($this);
        $omiseConfig   = $objectManager->getObject(OmiseConfig::class, [ 'helper' => $this->helperGetConfigAlwaysReturnFalse ]);

        $this->assertFalse($omiseConfig->isSandboxEnabled());
    }

    public function testIs3DSecureEnabled_configIsEnabled_mustReturnTrue()
    {
        $objectManager = new MagentoHelperObjectManager($this);
        $omiseConfig   = $objectManager->getObject(OmiseConfig::class, [ 'helper' => $this->helperGetConfigAlwaysReturnTrue ]);

        $this->assertTrue($omiseConfig->is3DSecureEnabled());
    }

    public function testIs3DSecureEnabled_configIsDisabled_mustReturnFalse()
    {
        $objectManager = new MagentoHelperObjectManager($this);
        $omiseConfig   = $objectManager->getObject(OmiseConfig::class, [ 'helper' => $this->helperGetConfigAlwaysReturnFalse ]);

        $this->assertFalse($omiseConfig->is3DSecureEnabled());
    }
}

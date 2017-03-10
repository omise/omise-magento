<?php
namespace Omise\Payment\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as MagentoHelperObjectManager;
use Magento\Framework\App\Config as MagentoConfig;

use Omise\Payment\Model\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function return_true_if_sanbox_mode_is_enabled()
    {
        $magento_config = $this->getMock(MagentoConfig::class, [], [], '', false);
        $magento_config->expects($this->any())
            ->method('getValue')
            ->willReturn(true);

        $config = (new MagentoHelperObjectManager($this))->getObject(
            Config::class,
            [ 'scopeConfig' => $magento_config ]
        );

        // Assert.
        $this->assertTrue($config->isSandboxEnabled());
    }

    /**
     * @test
     */
    public function return_false_if_sanbox_mode_is_disabled()
    {
        $magento_config = $this->getMock(MagentoConfig::class, [], [], '', false);
        $magento_config->expects($this->any())
            ->method('getValue')
            ->willReturn(false);

        $config = (new MagentoHelperObjectManager($this))->getObject(
            Config::class,
            [ 'scopeConfig' => $magento_config ]
        );

        // Assert.
        $this->assertFalse($config->isSandboxEnabled());
    }

    /**
     * @test
     */
    public function return_true_if_three_d_secure_is_enabled()
    {
        $magento_config = $this->getMock(MagentoConfig::class, [], [], '', false);
        $magento_config->expects($this->any())
            ->method('getValue')
            ->willReturn(true);

        $config = (new MagentoHelperObjectManager($this))->getObject(
            Config::class,
            [ 'scopeConfig' => $magento_config ]
        );

        // Assert.
        $this->assertTrue($config->is3DSecureEnabled());
    }

    /**
     * @test
     */
    public function return_false_if_three_d_secure_is_disabled()
    {
        $magento_config = $this->getMock(MagentoConfig::class, [], [], '', false);
        $magento_config->expects($this->any())
            ->method('getValue')
            ->willReturn(false);

        $config = (new MagentoHelperObjectManager($this))->getObject(
            Config::class,
            [ 'scopeConfig' => $magento_config ]
        );

        // Assert.
        $this->assertFalse($config->is3DSecureEnabled());
    }
}

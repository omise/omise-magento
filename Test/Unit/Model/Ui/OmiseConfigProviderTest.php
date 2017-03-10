<?php
namespace Omise\Payment\Test\Unit\Model\Ui;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as MagentoHelperObjectManager;
use Magento\Payment\Model\CcConfig;
use Omise\Payment\Model\Config;
use Omise\Payment\Model\Ui\OmiseConfigProvider;

class OmiseConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function retrieve_config()
    {
        $ccConfig = $this->getMock(CcConfig::class, [], [], '', false);
        $config   = $this->getMock(Config::class, [], [], '', false);

        $ccConfig->expects($this->any())
            ->method('getCcMonths')
            ->willReturn([]);

        $ccConfig->expects($this->any())
            ->method('getCcYears')
            ->willReturn([]);

        $config->expects($this->any())
            ->method('getPublicKey')
            ->willReturn('pkey_test_mock');

        $omiseConfigProvider = (new MagentoHelperObjectManager($this))->getObject(
            OmiseConfigProvider::class,
            [
                'ccConfig' => $ccConfig,
                'config'   => $config
            ]
        );

        $expected = [
            'payment' => [
                'ccform' => [
                    'months' => ['omise' => []],
                    'years'  => ['omise' => []],
                ],
                'omise' => [
                    'publicKey' => 'pkey_test_mock',
                ],
            ]
        ];

        // Assert.
        $this->assertEquals($expected, $omiseConfigProvider->getConfig());
    }
}

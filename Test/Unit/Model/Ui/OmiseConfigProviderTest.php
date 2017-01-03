<?php
namespace Omise\Payment\Test\Unit\Model\Ui;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as MagentoHelperObjectManager;
use Magento\Payment\Model\CcConfig;
use Omise\Payment\Model\OmiseConfig;
use Omise\Payment\Model\Ui\OmiseConfigProvider;

class OmiseConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Omise\Payment\Model\Ui\OmiseConfigProvider
     */
    protected $omiseConfigProvider;

    protected function setUp()
    {
        // Mock \Magento\Payment\Model\CcConfig result
        $ccConfig = $this->getMock(CcConfig::class, [], [], '', false);
        $ccConfig->expects($this->any())
            ->method('getCcMonths')
            ->willReturn([]);

        $ccConfig->expects($this->any())
            ->method('getCcYears')
            ->willReturn([]);

        // Mock \Omise\Payment\Model\OmiseConfig result
        $omiseConfig = $this->getMock(OmiseConfig::class, [], [], '', false);
        $omiseConfig->expects($this->any())
            ->method('getPublicKey')
            ->willReturn('pkey_test_mock');

        $objectManager = new MagentoHelperObjectManager($this);

        // Build \Omise\Payment\Model\Ui\OmiseConfigProvider instance
        $this->omiseConfigProvider = $objectManager->getObject(
            OmiseConfigProvider::class,
            [
                'ccConfig'    => $ccConfig,
                'omiseConfig' => $omiseConfig
            ]
        );
    }

    public function testGetConfig_retrieveConfigFromOmiseConfig_returnProperData()
    {
        $expected = [
            'payment' => [
                'ccform' => [
                    'months' => ['omise' => []],
                    'years' => ['omise' => []],
                ],
                'omise' => [
                    'publicKey' => 'pkey_test_mock',
                ],
            ]
        ];
        $actual   = $this->omiseConfigProvider->getConfig();

        $this->assertEquals($expected, $actual);
    }
}

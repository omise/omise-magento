<?php
namespace Omise\Payment\Test\Unit\Plugin;

use Omise\Payment\Plugin\CsrfValidatorSkip;
use Omise\Payment\Block\Adminhtml\System\Config\Form\Field\Webhook;
use PHPUnit\Framework\TestCase;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ActionInterface;

class CsrfValidatorSkipTest extends TestCase
{
    /**
     * @var UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlInterface;

    /**
     * @var CsrfValidatorSkip
     */
    protected $plugin;

    protected function setUp(): void
    {
        $this->urlInterface = $this->createMock(UrlInterface::class);
        $this->plugin = new CsrfValidatorSkip($this->urlInterface);
    }

    /**
     * @covers \Omise\Payment\Plugin\CsrfValidatorSkip::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(CsrfValidatorSkip::class, $this->plugin);
    }

    /**
     * @covers \Omise\Payment\Plugin\CsrfValidatorSkip::__construct
     * @covers \Omise\Payment\Plugin\CsrfValidatorSkip::aroundValidate
     */
    public function testAroundValidateSkipsCsrf(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $action = $this->createMock(ActionInterface::class);

        $request->method('getModuleName')->willReturn('omise');
        $this->urlInterface->method('getCurrentUrl')->willReturn('https://example.com/' . Webhook::URI);

        // Real closure, should NOT be called
        $proceed = function ($req, $act) {
            throw new \Exception('Proceed should not be called for omise webhook');
        };

        // Call plugin
        $this->plugin->aroundValidate($this->createMock(\stdClass::class), $proceed, $request, $action);

        // If no exception, test passes
        $this->assertTrue(true);
    }

    /**
     * @covers \Omise\Payment\Plugin\CsrfValidatorSkip::__construct
     * @covers \Omise\Payment\Plugin\CsrfValidatorSkip::aroundValidate
     */
    public function testAroundValidateCallsProceed(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $action = $this->createMock(ActionInterface::class);

        $request->method('getModuleName')->willReturn('other_module');
        $this->urlInterface->method('getCurrentUrl')->willReturn('https://example.com/something');

        $called = false;
        $proceed = function ($req, $act) use (&$called, $request, $action) {
            $called = true;
            $this->assertSame($request, $req);
            $this->assertSame($action, $act);
        };

        $this->plugin->aroundValidate($this->createMock(\stdClass::class), $proceed, $request, $action);

        $this->assertTrue($called, 'Proceed should have been called');
    }
}
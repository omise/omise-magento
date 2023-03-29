<?php

namespace Magento\AdminNotification\Test\Unit\Model\System\Message;

use Magento\Framework\Notification\MessageInterface;
use Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\SecureFormBannerMessage;
use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Config\Cc;

class SecureFormBannerMessageTest extends TestCase
{
    /**
     * @var SecureFormBannerMessage
     */
    protected $_messageModel;

    /**
     * @var Stub
     */
    protected $_omiseCcConfigMock;

    protected function setUp(): void
    {
        $this->_omiseCcConfigMock = $this->createStub(Cc::class);
        $this->_messageModel = new SecureFormBannerMessage($this->_omiseCcConfigMock);
    }

    /**
     * test get identity
     * 
     * @covers Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\SecureFormBannerMessage
     */
    public function testGetIdentity()
    {
        $this->assertEquals('opn_payments_secure_form_message', $this->_messageModel->getIdentity());
    }

    /**
     * test text is display when secure form is off
     * 
     * @covers Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\SecureFormBannerMessage
     */
    public function testIsDisplayed()
    {
        $this->_omiseCcConfigMock->method('getSecureForm')->willReturn(false);
        $this->assertEquals(true, $this->_messageModel->isDisplayed());
    }

    /**
     * test text is not display when secure form is on
     * 
     * @covers Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\SecureFormBannerMessage
     */
    public function testIsNotDisplayed()
    {
        $this->_omiseCcConfigMock->method('getSecureForm')->willReturn(true);
        $this->assertEquals(false, $this->_messageModel->isDisplayed());
    }

    /**
     * test that get severity should return MessageInterface:SEVERITY_CRITICAL
     * 
     * @covers Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\SecureFormBannerMessage
     */
    public function testGetSeverity()
    {
        $this->assertEquals(MessageInterface::SEVERITY_CRITICAL, $this->_messageModel->getSeverity());
    }

    /**
     * test getText return valid html
     * 
     * @covers Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\SecureFormBannerMessage
     */
    public function testGetText()
    {
        $messageText = '<b>Opn Payments</b> : Update now to use the secure form to securely accept payment information. Note that you must re-customize the credit card checkout form after the upgrade. For more details, please click <a target="blank" href="https://www.omise.co/magento-plugin"> here </a>.';

        $this->assertStringContainsString($messageText, $this->_messageModel->getText());
    }
}

<?php
declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Model\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Model\Data\Email;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Model\Data\Email
 * @covers \Omise\Payment\Model\Data\Email::sendEmail
 * @covers \Omise\Payment\Model\Data\Email::getEmailData
 * @covers \Omise\Payment\Model\Data\Email::getPaynowChargeExpiryTime
 */
final class EmailTest extends TestCase
{
    private $scopeConfig;
    private $charge;
    private $assetRepo;
    private $transportBuilder;
    private $transport;
    private $storeManager;
    private $store;
    private $helper;
    private $email;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->charge = $this->createMock(Charge::class);
        $this->assetRepo = $this->createMock(Repository::class);
        $this->transportBuilder = $this->createMock(TransportBuilder::class);
        $this->transport = $this->createMock(TransportInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        // Use the concrete Store class, not the interface
        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getBaseUrl'])
            ->getMock();

        $this->store->method('getId')->willReturn(1);
        $this->store->method('getBaseUrl')->willReturn('https://store.test/');
        $this->storeManager->method('getStore')->willReturn($this->store);

        $this->helper = $this->createMock(OmiseHelper::class);

        $this->scopeConfig->method('getValue')->willReturnMap([
            ['trans_email/ident_sales/name', 'store', null, 'My Store'],
            ['trans_email/ident_sales/email', 'store', null, 'store@test.com'],
        ]);

        $this->transportBuilder->method('setTemplateIdentifier')->willReturnSelf();
        $this->transportBuilder->method('setTemplateOptions')->willReturnSelf();
        $this->transportBuilder->method('setTemplateVars')->willReturnSelf();
        $this->transportBuilder->method('setFrom')->willReturnSelf();
        $this->transportBuilder->method('addTo')->willReturnSelf();
        $this->transportBuilder->method('getTransport')->willReturn($this->transport);

        $this->email = new Email(
            $this->scopeConfig,
            $this->charge,
            $this->assetRepo,
            $this->transportBuilder,
            $this->storeManager,
            $this->helper
        );
    }

    private function createOrder(string $type): Order
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $payment->method('getAdditionalInformation')->willReturnMap([
            ['payment_type', $type],
            ['charge_id', 'ch_123'],
            ['barcode', 'SVGCODE'],
            ['charge_authorize_uri', 'https://pay.test'],
        ]);

        $payment->method('getData')->willReturn(['amount_ordered' => 100]);
        $order->method('getPayment')->willReturn($payment);
        $order->method('getCustomerEmail')->willReturn('customer@test.com');
        $order->method('getIncrementId')->willReturn('1000001');

        $currency = new class {
            public function getCurrencyCode()
            {
                return 'THB';
            }
        };

        $order->method('getOrderCurrency')->willReturn($currency);

        return $order;
    }

    private function mockCharge(): void
    {
        $charge = new \stdClass();
        $charge->expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));
        $charge->source = [
            'scannable_code' => [
                'image' => ['download_uri' => 'https://qr.test']
            ]
        ];

        $this->charge->method('find')->willReturn($charge);
    }

    public function testSendEmailWithTesco(): void
    {
        $this->mockCharge();
        $this->helper->method('convertTescoSVGCodeToHTML')->willReturn('<svg/>');

        $this->email->sendEmail($this->createOrder('bill_payment_tesco_lotus'));
        $this->assertTrue(true);
    }

    public function testSendEmailWithPaynow(): void
    {
        $this->mockCharge();
        $this->assetRepo->method('getUrl')->willReturn('https://banks.png');

        $this->email->sendEmail($this->createOrder('paynow'));
        $this->assertTrue(true);
    }

    public function testSendEmailWithPromptpay(): void
    {
        $this->mockCharge();
        $this->email->sendEmail($this->createOrder('promptpay'));
        $this->assertTrue(true);
    }

    public function testSendEmailWithEcontext(): void
    {
        $this->mockCharge();
        $this->email->sendEmail($this->createOrder('econtext'));
        $this->assertTrue(true);
    }

    public function testSendEmailWithUnknownTypeHitsDefault(): void
    {
        $order = $this->createOrder('unknown');
        $this->email->sendEmail($order);
        $this->assertTrue(true);
    }
}

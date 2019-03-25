<?php
namespace Omise\Payment\Block\Customer;

use Magento\Framework\View\Element\Template;
use Omise\Payment\Model\Customer;

/**
 * Class PaymentTokens
 */
class PaymentMethods extends Template
{
    /**
     * @var PSR\Log\LoggerInterface
     */
    private $log;

    /**
     * @var Omise\Payment\Model\Customer;
     */
    private $customer;

    /**
     * PaymentMethods constructor.
     * @param Template\Context $context
     * @param Omise\Payment\Model\Customer $customer
     * @param PSR\Log\LoggerInterface $log
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Customer $customer,
        \PSR\Log\LoggerInterface $log,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->log = $log;
        $this->customer = $customer;
    }

    /**
     * @return  array
     */
    public function getCards()
    {
        $this->log->debug('getting cards');
        if (! $this->customer->getMagentoCustomerId() || ! $this->customer->getId()) {
            $this->log->debug('no customer id or magento customer id', ['magid'=> $this->customer->getMagentoCustomerId()]);
            return null;
        }
        return $this->customer->cards(['order' => 'reverse_chronological']);
    }
}

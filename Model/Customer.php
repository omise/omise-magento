<?php
namespace Omise\Payment\Model;

use Magento\Customer\Model\Session as MagentoCustomerSession;
use Omise\Payment\Model\Api\Customer as OmiseCustomer;
use Omise\Payment\Model\Omise;

class Customer
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $magentoCustomerSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Omise\Payment\Model\Api\Customer
     */
    protected $omiseCustomer;

    /**
     * @var \Omise\Payment\Model\Omise
     */
    protected $omise;

    public function __construct(
        MagentoCustomerSession $magentoCustomerSession,
        Omise                  $omise,
        OmiseCustomer          $omiseCustomer
    ) {
        $this->magentoCustomerSession = $magentoCustomerSession;
        $this->customer               = $this->magentoCustomerSession->getCustomer();
        $this->omise                  = $omise;
        $this->omiseCustomer          = $omiseCustomer;

        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();
    }

    public function create()
    {
        return $this->omiseCustomer->create([
            'email'       => $this->customer->getEmail(),
            'description' => trim($this->customer->getFirstname() . ' ' . $this->customer->getLastname())
        ]);
    }
}

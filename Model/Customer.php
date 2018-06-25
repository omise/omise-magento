<?php
namespace Omise\Payment\Model;

use Magento\Customer\Model\Session as MagentoCustomerSession;
use Omise\Payment\Model\Api\Customer as OmiseCustomer;
use Omise\Payment\Model\Omise;

class Customer
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $magentoCustomerResource;

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
        \Magento\Customer\Model\ResourceModel\Customer $magentoCustomerResource,
        MagentoCustomerSession $magentoCustomerSession,
        Omise                  $omise,
        OmiseCustomer          $omiseCustomer
    ) {
        $this->magentoCustomerResource = $magentoCustomerResource;
        $this->magentoCustomerSession  = $magentoCustomerSession;
        $this->customer                = $this->magentoCustomerSession->getCustomer();
        $this->omise                   = $omise;
        $this->omiseCustomer           = $omiseCustomer;

        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();
    }

    public function createOmiseCustomer($cardToken)
    {
        $omiseCustomer = $this->omiseCustomer->create([
            'email'       => $this->customer->getEmail(),
            'description' => trim($this->customer->getFirstname() . ' ' . $this->customer->getLastname()),
            'card'        => $cardToken
        ]);

        $this->customer->setData('omise_customer_id', $omiseCustomer->id);
        $this->magentoCustomerResource->saveAttribute($this->customer, 'omise_customer_id');

        return $omiseCustomer;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->customer->getData('omise_customer_id');
    }

    public function cards()
    {
        $customer = $this->omiseCustomer->find($this->customer->getData('omise_customer_id'));

        return $customer->cards();
    }
}

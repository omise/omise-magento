<?php
namespace Omise\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Omise\Payment\Helper\OmiseHelper;

class ThreeDSecureDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    const PROCESS_3DSECURE = 'process_3dsecure';

    /**
     * @var string
     */
    const RETURN_URI = 'return_uri';

    /**
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    protected $omiseHelper;

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     */
    public function __construct(OmiseHelper $omiseHelper)
    {
        $this->omiseHelper = $omiseHelper;
    }

    /**
     * Build request parameters
     *
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        if ($this->is3DSecureEnabled()) {
            return $this->buildRequestWith3DSecure($buildSubject);
        }

        return $this->buildRequestWithout3DSecure($buildSubject);
    }

    /**
     * Check whether 3-D Secure config enable or not.
     *
     * @return boolean
     */
    protected function is3DSecureEnabled()
    {
        if ($this->omiseHelper->getConfig('3ds')) {
            return true;
        }

        return false;
    }

    /**
     * Build request parameters with 3-D Secure process required.
     *
     * @param  array $buildSubject
     *
     * @return array
     */
    protected function buildRequestWith3DSecure(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $method  = $payment->getPayment();
        $method->setAdditionalInformation(self::PROCESS_3DSECURE, true);

        return [
            self::PROCESS_3DSECURE => true,
            self::RETURN_URI       => $this->omiseHelper->getUrl('omise/omise/callback', ['_secure' => true])
        ];
    }

    /**
     * Build request parameters without 3-D Secure process required.
     *
     * @param  array $buildSubject
     *
     * @return array
     */
    protected function buildRequestWithout3DSecure(array $buildSubject)
    {
        return [
            self::PROCESS_3DSECURE => false,
            self::RETURN_URI       => ""
        ];
    }
}
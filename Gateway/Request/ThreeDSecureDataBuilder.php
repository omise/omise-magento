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
    const RETURN_URI = 'return_uri';

    /**
     * @var string
     */
    const REQUIRE_REDIRECT = 'require_redirect';

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $omiseHelper
     */
    public function __construct(OmiseHelper $omiseHelper)
    {
        $this->omiseHelper = $omiseHelper;
    }

    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        if ($this->is3DSecureEnabled()) {
            return [
                self::RETURN_URI       => $this->omiseHelper->getUrl('/'),
                self::REQUIRE_REDIRECT => true
            ];
        }

        return [
            self::RETURN_URI       => "",
            self::REQUIRE_REDIRECT => false
        ];
    }

    /**
     * @return boolean
     */
    protected function is3DSecureEnabled()
    {
        if (! $this->omiseHelper->getConfig('3ds')) {
            return false;
        }

        return true;
    }
}

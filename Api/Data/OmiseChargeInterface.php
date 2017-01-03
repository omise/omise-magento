<?php
namespace Omise\Payment\Api\Data;

interface OmiseChargeInterface
{
    /*
     * Charge id
     */
    const ID = 'id';

    /*
     * Charge's authorized field
     */
    const CAPTURE = 'capture';

    /*
     * Charge's authorized field
     */
    const AUTHORIZED = 'authorized';

    /*
     * Charge's captured field
     */
    const CAPTURED = 'captured';

    /*
     * Charge's paid field
     */
    const PAID = 'paid';

    /*
     * Charge's authorize uri.
     */
    const AUTHORIZE_URI = 'authorize_uri';

    /**
     * Gets charge's authorize uri.
     *
     * @param  string $orderId
     *
     * @return string|null
     */
    public function getAuthorizeUri($orderId);
}
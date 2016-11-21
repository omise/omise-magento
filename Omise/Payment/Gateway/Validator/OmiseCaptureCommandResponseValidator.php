<?php
namespace Omise\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Validator\AbstractValidator;

class OmiseCaptureCommandResponseValidator extends AbstractValidator
{
    /**
     * @var string
     */
    protected $message;

    /**
     * Performs domain-related validation for business object
     *
     * @param  array $validationSubject
     *
     * @throws \Magento\Payment\Gateway\Command\CommandException
     *
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        /**
         * Note, normally we should return [$isValid = false, $errorMessages = ['msg_1', 'msg_2']]
         * out to the GatewayCommand::execute() method.
         * But since we couldn't overwrite the error message from CommandException.
         * We have to throw the CommandException by ourself here.
         *
         * For the parameter of $validationSubject['response'],
         * please see: Omise\Payment\Gateway\Http\Client\AbstractOmiseClient.
         */

        if (! $this->isClientRequestedSuccess($validationSubject)) {
            throw new CommandException(__($this->message));
        }

        $omise_object = $validationSubject['response']['data'];

        if (! $this->isReponseOmiseObject($omise_object)
            || ! $this->validateAuthorizedAndCapturedCharge($omise_object)) {
            throw new CommandException(__($this->message));
        }

        return $this->createResult(true, []);
    }

    /**
     * @param  array $validationSubject
     *
     * @return boolean
     */
    protected function isValidResponse(array $validationSubject)
    {
        if (! isset($validationSubject['response']) || $validationSubject['response']['object'] !== "omise") {
            $this->message = 'Transaction has been declined. Please contact administrator';
            return false;
        }

        return true;
    }

    /**
     * @param  array $validationSubject
     *
     * @return boolean
     */
    protected function isClientRequestedSuccess(array $validationSubject)
    {
        if (! $this->isValidResponse($validationSubject)) {
            return false;
        }

        $response = $validationSubject['response'];
        if ($response['status'] === 'failed') {
            $this->message = $response['message'];
            return false;
        }

        return true;
    }

    /**
     * @param  \OmiseApiResource $omise
     *
     * @return boolean
     */
    protected function isReponseOmiseObject($omise)
    {
        if (! isset($omise['object']) || $omise['object'] !== 'charge') {
            $this->message = "Couldn't retrieve charge transaction. Please contact administrator.";
            return false;
        }

        return true;
    }

    /**
     * @param  \OmiseApiResource $omise
     *
     * @return boolean
     */
    protected function validateAuthorizedAndCapturedCharge($omise)
    {
        // Support Omise API 2014-07-27
        $paid = isset($omise['paid']) ? $omise['paid'] : $omise['captured'];
        if (! $omise['authorized'] || ! $paid) {
            $this->message = $this->getOmiseFailureMessage($omise);
            return false;
        }

        return true;   
    }

    /**
     * @param  \OmiseApiResource $omise
     *
     * @return string
     */
    protected function getOmiseFailureMessage($omise)
    {
        if (isset($omise['failure_message']) && $omise['failure_message'] !== "") {
            return $omise['failure_message'];
        }

        return "We couldn't proceed charge well, some part of the process is failed. Please confirm your order with adminstrator.";
    }
}

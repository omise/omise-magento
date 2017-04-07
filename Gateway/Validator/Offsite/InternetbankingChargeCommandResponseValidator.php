<?php
namespace Omise\Payment\Gateway\Validator\Offsite;

use Magento\Payment\Gateway\Validator\AbstractValidator;

class InternetbankingChargeCommandResponseValidator extends AbstractValidator
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
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (! isset($validationSubject['response']) || $validationSubject['response']['object'] !== 'omise') {
            return $this->failed(__('Transaction has been declined, please contact our support if you have any questions'));
        }

        if ($validationSubject['response']['status'] === 'failed') {
            return $this->failed(__($validationSubject['response']['message']));
        }

        $charge = $validationSubject['response']['data'];

        if (! isset($charge['object']) || $charge['object'] !== 'charge') {
            return $this->failed(__('Couldn\'t retrieve charge transaction, please contact our support if you have any questions.'));
        }

        if ($charge['status'] === 'failed') {
            return $this->failed(__('Payment failed. ' . ucfirst($charge['failure_message']) . ', please contact our support if you have any questions.'));
        }

        $captured = $charge['captured'] ? $charge['captured'] : $charge['paid'];

        if ($charge['status'] === 'pending'
            && $charge['authorized'] == false
            && $captured == false
            && $charge['authorize_uri']
        ) {
            return $this->createResult(true, []);
        }

        return $this->failed(__('Payment failed, invalid payment status, please contact our support if you have any questions'));
    }

    /**
     * @param  \Magento\Framework\Phrase|string $message
     *
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    protected function failed($message)
    {
        return $this->createResult(false, [ $message ]);
    }
}

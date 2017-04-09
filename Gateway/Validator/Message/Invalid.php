<?php
namespace Omise\Payment\Gateway\Validator\Message;

use Magento\Framework\Phrase;

class Invalid
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @param string $message
     */
    public function __construct($message)
    {
        if (! $this->message) {
            $this->message = $message;
        }
    }

    public function getMessage()
    {
        return new Phrase($this->message);
    }
}

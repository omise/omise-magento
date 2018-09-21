<?php
namespace Omise\Payment\Model;
 
class Transport extends \Zend_Mail_Transport_Smtp implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * @var \Magento\Framework\Mail\MessageInterface
     */
    protected $_message;
 
    /**
     * @param MessageInterface $message
     * @param null $parameters
     * @throws \InvalidArgumentException
     */
    public function __construct(\Magento\Framework\Mail\MessageInterface $message, \PSR\Log\LoggerInterface $log)
    {
        $log->debug('trying to send message by custom shit');
        if (!$message instanceof \Zend_Mail) {
            throw new \InvalidArgumentException('The message should be an instance of \Zend_Mail');
        }
         $smtpHost= 'smtp.gmail.com';//your smtp host  ';
         $smtpConf = [
            'auth' => 'login',//auth type
            'tsl' => 'tsl', 
            'port' => '587',
            'username' => 'loteneka@gmail.com',//smtm user name
            'password' => 'Haniaxp1!'//smtppassword 
         ];
 
        parent::__construct($smtpHost, $smtpConf);
        $this->_message = $message;
    }
 
    /**
     * Send a mail using this transport
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendMessage()
    {
        try {
            parent::send($this->_message);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\MailException(new \Magento\Framework\Phrase($e->getMessage()), $e);
        }
    }
}
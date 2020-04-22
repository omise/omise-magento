<?php
namespace Omise\Payment\Plugin;

class CsrfValidatorSkip
{
    protected $urlInterface;
    public function __construct(\Magento\Framework\UrlInterface $urlInterface) {
        $this->urlInterface = $urlInterface;
    }
    /**
     * @param \Magento\Framework\App\Request\CsrfValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ActionInterface $action
     */
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        if ($request->getModuleName() == 'omise' && strpos($this->urlInterface->getCurrentUrl(), 'omise/callback/webhook')) {
            return;
        }
        $proceed($request, $action);
    }
}
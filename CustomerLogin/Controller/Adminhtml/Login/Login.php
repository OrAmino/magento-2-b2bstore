<?php

namespace Orienteed\CustomerLogin\Controller\Adminhtml\Login;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Integration\Model\Oauth\TokenFactory;
use Orienteed\CustomerLogin\Helper\Data;

class Login extends Action
{
    protected $_loginHelper;
    protected $_resultFactory;

    public function __construct(
        Context $context,
        Data $helper,
        ResultFactory $resultFactory,
        TokenFactory $tokenModelFactory
    ) {
        parent::__construct($context);
        $this->_tokenModelFactory   = $tokenModelFactory;
        $this->_loginHelper         = $helper;
        $this->_resultFactory       = $resultFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $url = '/';

        if ($this->_loginHelper->isEnabled()) {
            $customerId = $this->getRequest()->getParam('id');
            $customerToken = $this->_tokenModelFactory->create();
            $token = $customerToken->createCustomerToken($customerId)->getToken();
            $url   = $this->_loginHelper->getStoreFrontUrl() . "/" . $token;
        }

        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }
}

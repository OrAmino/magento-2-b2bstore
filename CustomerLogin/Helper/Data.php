<?php

namespace Orienteed\CustomerLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    const XML_CUSTOMER_LOGIN_ENABLED = 'customerlogin/general/customerloginenabled';
    const XML_CUSTOMER_STORE_URL     = 'customerlogin/general/customerstoreurl';

    protected $encryptor;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    public function isEnabled($storeId = 0)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_CUSTOMER_LOGIN_ENABLED,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }

    public function getStoreFrontUrl($storeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_CUSTOMER_STORE_URL,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }
}

<?php

namespace Orienteed\RequiredLogin\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_REQUIRED_LOGIN_ENABLE = 'requiredlogin/general/enable';

    /**
     * is login required
     *
     * @return bool
     */
    public function isLoginRequired($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_REQUIRED_LOGIN_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}

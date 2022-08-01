<?php

namespace Orienteed\GraphQl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const MPSAVECART_FRONTEND_URL = 'mpsavecart/general/frontend_url';

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function getStoreFrontUrl($storeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::MPSAVECART_FRONTEND_URL,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }
}

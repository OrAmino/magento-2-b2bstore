<?php

namespace Orienteed\CustomerLogin\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Orienteed\CustomerLogin\Helper\Data;

class CustomerLogin extends GenericButton implements ButtonProviderInterface
{
    protected $_helper;

    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper
    ) {
        parent::__construct($context, $registry);
        $this->_helper = $helper;
    }

    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data       = [];
        if ($this->_helper->isEnabled() && $customerId) {
            $data = [
                'label'      => __('Login as PWA Customer'),
                'class'      => 'loginascustomer',
                'on_click'   => sprintf("window.open('%s');", $this->getLoginUrl()),
                'sort_order' => 60,
            ];
        }
        return $data;
    }


    public function getLoginUrl()
    {
        return $this->getUrl('orienteedcustomerlogin/login/login', ['id' => $this->getCustomerId()]);
    }
}

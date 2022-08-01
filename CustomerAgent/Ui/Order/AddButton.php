<?php

namespace Orienteed\CustomerAgent\Ui\Order;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Backend\Model\Auth\Session;

class AddButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Session
     */
    private $_backendSession;

    /**
     * AddButton constructor.
     *
     * @param Context $context
     * @param AuthorizationInterface $authorization
     * @param Session $backendSession
     */
    public function __construct(
        Context $context,
        AuthorizationInterface $authorization,
        Session $backendSession
    ) {
        $this->urlBuilder      = $context->getUrlBuilder();
        $this->authorization   = $authorization;
        $this->_backendSession = $backendSession;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->authorization->isAllowed('Magento_Sales::create')) {
            return [];
        }

        if ($this->getAdminUserRole() == 3) {
            return [];
        }

        return [
            'label'      => __('Create New Order'),
            'on_click'   => sprintf("location.href = '%s';", $this->getUrl('sales/order_create/start')),
            'class'      => 'primary',
            'sort_order' => 10
        ];
    }

    public function getAdminUserRole()
    {
        if ($adminUser = $this->_backendSession->getUser()) {
            $roleId = $adminUser->getRole()->getRoleId();

            return $roleId;
        }

        return false;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}

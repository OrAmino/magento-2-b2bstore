<?php
/**
 * Customer Grid Collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Orienteed\CustomerAgent\Plugin\Sales\Model\ResourceModel\Order\Customer;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Customer\Collection
{
    const CUSTOMER_MODEL_NAME = \Magento\Customer\Model\Customer::class;

    const CHECK_URL = 'mprequestforquote/quote_create';

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $_urlInterface,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Orienteed\CustomerAgent\Helper\Data $customerAgentHelperData,
        $connection = null,
        $modelName = self::CUSTOMER_MODEL_NAME
    ) {
        $this->_urlInterface = $_urlInterface;
        $this->authSession = $authSession;
        $this->customerAgentHelperData = $customerAgentHelperData;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $entitySnapshot,
            $fieldsetConfig,
            $storeManager,
            $connection,
            $modelName
        );
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addAttributeToSelect(
            'customer_agent'
        );

        return $this;
    }

    protected function _renderFiltersBefore()
    {
        $roleId = $this->customerAgentHelperData->getAgentRole();
        if(strpos($this->_urlInterface->getRouteUrl(), self::CHECK_URL) !== false && $this->authSession->getUser() && $roleId){
            $userId = $this->authSession->getUser()->getId();
            if($userId > 1){
                $this->addAttributeToFilter('customer_agent', $userId);
            }
        }
    }
}

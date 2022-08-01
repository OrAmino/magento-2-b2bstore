<?php

namespace Orienteed\CustomerAgent\Model\ResourceModel\Customer\Grid;

use Magento\Customer\Model\ResourceModel\Grid\Collection as OriginalCollection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Locale\ResolverInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory as RoleUserCollectionFactory;

class Collection extends OriginalCollection
{
    protected $_authSession;

    protected $logger;

    /**
     * @var RoleUserCollectionFactory
     */
    protected $roleUserCollectionFactory;

    /**
     * @var \Orienteed\CustomerAgent\Helper\Data
     */
    protected $helperData;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        ResolverInterface $localeResolver,
        \Magento\Backend\Model\Auth\Session $authSession,
        RoleUserCollectionFactory $roleUserCollectionFactory,
        \Orienteed\CustomerAgent\Helper\Data $helperData
    ) {
        $this->_authSession = $authSession;
        $this->logger       = $logger;
        $this->helperData   = $helperData;
        $this->roleUserCollectionFactory = $roleUserCollectionFactory;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $localeResolver);
    }

    protected function _renderFiltersBefore()
    {
        $user = $this->_authSession->getUser();
        $loggedUserId =  $user->getId();
        $roleId = $this->helperData->getAgentRole();

        $agentUserIds = [];
        if ($roleId) {
            $collection = $this->roleUserCollectionFactory->create();
            $_collection = $collection->addFieldToFilter('detail_role.role_id', $roleId);;

            foreach ($_collection as $_coll) {
                $agentUserIds[] = $_coll->getUserId();
            }
        }

        if (in_array($loggedUserId, $agentUserIds)) {
            $this->getSelect()->where('customer_agent=' . $loggedUserId);
        }
        parent::_renderFiltersBefore();
    }
}

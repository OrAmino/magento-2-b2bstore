<?php

namespace Orienteed\CustomerAgent\Model\ResourceModel\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory as RoleUserCollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

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

    /**
     * @var CustomerCollectionFactory
     */
    protected $_customerCollectionFactory;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        ResolverInterface $localeResolver,
        \Magento\Backend\Model\Auth\Session $authSession,
        RoleUserCollectionFactory $roleUserCollectionFactory,
        \Orienteed\CustomerAgent\Helper\Data $helperData,
        CustomerCollectionFactory $customerCollectionFactory
    ) {
        $this->_authSession               = $authSession;
        $this->logger                     = $logger;
        $this->helperData                 = $helperData;
        $this->roleUserCollectionFactory  = $roleUserCollectionFactory;
        $this->_customerCollectionFactory = $customerCollectionFactory;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager);
    }

    protected function _renderFiltersBefore()
    {
        $user = $this->_authSession->getUser();
        $loggedUserId =  $user->getId();
        $roleId = $this->helperData->getAgentRole();

        $agentUserIds = [];
        if ($roleId) {
            $collection = $this->roleUserCollectionFactory->create();
            $_collection = $collection->addFieldToFilter('detail_role.role_id', $roleId);

            foreach ($_collection as $_coll) {
                $agentUserIds[] = $_coll->getUserId();
            }
        }

        if (in_array($loggedUserId, $agentUserIds)) {
            $customerCollection = $this->_customerCollectionFactory->create()
                ->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("customer_agent", array("EQ" => $loggedUserId));

            $agentCustomersId = [];
            if (count($customerCollection)) {
                foreach ($customerCollection as $customers) {
                    $agentCustomersId[] = $customers->getId();
                }
            }

            if (array_filter($agentCustomersId)) {
                $this->getSelect()->where('customer_id IN (' . implode(",", $agentCustomersId) . ')');
            } else {
                $this->getSelect()->where('customer_id=0');
            }
        }
        parent::_renderFiltersBefore();
    }
}

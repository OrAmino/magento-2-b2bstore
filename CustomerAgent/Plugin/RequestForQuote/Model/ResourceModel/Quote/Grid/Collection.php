<?php

namespace Orienteed\CustomerAgent\Plugin\RequestForQuote\Model\ResourceModel\Quote\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends \Mageplaza\RequestForQuote\Model\ResourceModel\Quote\Grid\Collection
{
    protected $authSession;

    protected $customerAgentHelperData;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Orienteed\CustomerAgent\Helper\Data $customerAgentHelperData,
        $mainTable = 'mageplaza_requestforquote_quote'
    ) {
        $this->authSession = $authSession;
        $this->customerAgentHelperData = $customerAgentHelperData;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable);
    }

    /**
     * @return SearchResult|void
     */
    protected function _initSelect()
    {
        $userId = 1;
        $roleId = $this->customerAgentHelperData->getAgentRole();
        if ($this->authSession->getUser()) {
            $userId = $this->authSession->getUser()->getId();
        }

        parent::_initSelect();

        if ($userId > 1 && $roleId) {
            $this->getSelect()->where('is_active = 1 and agent_id = ' . $userId);
        } else {
            $this->getSelect()->where('is_active = 1');
        }
    }
}

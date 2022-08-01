<?php

namespace Orienteed\CustomerAgent\Model\Customer\Attribute\Source;

use Magento\User\Model\ResourceModel\User\CollectionFactory as RoleUserCollectionFactory;

class AgentList extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    protected $_authSession;

    /**
     * @var RoleUserCollectionFactory
     */
    protected $roleUserCollectionFactory;

    /**
     * @var \Orienteed\CustomerAgent\Helper\Data
     */
    protected $helperData;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param RoleUserCollectionFactory $roleUserCollectionFactory
     * @param \Magento\Framework\Convert\DataObject $converter
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        RoleUserCollectionFactory $roleUserCollectionFactory,
        \Orienteed\CustomerAgent\Helper\Data $helperData,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->_authSession = $authSession;
        $this->roleUserCollectionFactory = $roleUserCollectionFactory;
        $this->helperData   = $helperData;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $agents = [];

        $user = $this->_authSession->getUser();
        if ($user) {
            $loggedUserId =  $user->getId();
            $roleId       = $this->helperData->getAgentRole();
            $collection   = $this->roleUserCollectionFactory->create();

            $agentUserIds = [];
            if ($roleId) {
                $collection->addFieldToFilter('detail_role.role_id', $roleId);
                foreach ($collection as $_coll) {
                    $agentUserIds[] = $_coll->getUserId();
                }
            }

            if (!in_array($loggedUserId, $agentUserIds)) {
                $agents[] = [
                    'value' => '', 'label' => __('Select Agent')
                ];
                foreach ($collection as $_collection) {

                    $agents[] = [
                        'value' => $_collection->getUserId(), 'label' => $_collection->getUsername()
                    ];
                }
            } else {
                $agents[] = [
                    'value' => $loggedUserId, 'label' => $user->getUsername()
                ];
            }
        }else{
            $roleId       = $this->helperData->getAgentRole();
            $collection   = $this->roleUserCollectionFactory->create();
            foreach ($collection as $_collection) {

                $agents[] = [
                    'value' => $_collection->getUserId(), 'label' => $_collection->getUsername()
                ];
            }
        }

        return $agents;
    }
}

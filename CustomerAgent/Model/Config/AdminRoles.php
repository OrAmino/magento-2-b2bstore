<?php

namespace Orienteed\CustomerAgent\Model\Config;

use Magento\Framework\Option\ArrayInterface;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;

class AdminRoles implements ArrayInterface
{
    private $rolesFactory;

    public function __construct(
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $rolesFactory
    ) {
        $this->rolesFactory = $rolesFactory;
    }

    public function toOptionArray()
    {
        $collection = $this->rolesFactory->create();
        $collection->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE);
        $roles   = [];
        $roles[] = [
            'value' => '', 'label' => __('Select Role')
        ];
        foreach ($collection as $coll) {
            $roles[] = [
                'value' => $coll->getRoleId(), 'label' => $coll->getRoleName()
            ];
        }

        return $roles;
    }
}

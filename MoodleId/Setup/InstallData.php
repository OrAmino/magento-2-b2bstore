<?php

namespace Orienteed\MoodleId\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;

class InstallData implements InstallDataInterface
{
    const ID_COLUMN = "moodle_id";

    protected $_customerSetupFactory;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->_customerSetupFactory = $customerSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;

        $customerSetup = $this->_customerSetupFactory->create(['setup' => $setup]);
        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::ID_COLUMN,
            [
                'type'         => 'text',
                'label'        => 'Moodle Id',
                'input'        => 'text',
                'required'     => false,
                'visible'      => true,
                'user_defined' => true,
                'sort_order'   => 1000,
                'position'     => 1000,
                'system'       => 0,
            ]
        );
        
        $Attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::ID_COLUMN)
            ->addData([
                'attribute_set_id'   => 1,
                'attribute_group_id' => 1,
                'used_in_forms'      => ['adminhtml_customer', 'checkout_register', 'customer_account_create', 'customer_account_edit', 'adminhtml_checkout'],
            ]);

        $Attribute->save();

        $installer->endSetup();
    }
}

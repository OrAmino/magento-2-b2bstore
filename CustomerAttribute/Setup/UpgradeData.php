<?php

namespace Orienteed\CustomerAttribute\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @package Orienteed\CustomerAttribute\Setup
 * 
 * UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    const EXTERNAL_CLIENT_NUMBER  = "external_client_number";
    const CLIENT_NUMBER           = "client_number";
    const SALES_ORDER             = "sales_order";
    const SALES_ORDER_GRID        = "sales_order_grid";

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * __construct
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            if (!$customerSetup->getAttributeId(Customer::ENTITY, self::EXTERNAL_CLIENT_NUMBER)) {
                $customerSetup->addAttribute(
                    Customer::ENTITY,
                    self::EXTERNAL_CLIENT_NUMBER,
                    [
                        'type'         => 'varchar',
                        'label'        => 'External Client Number',
                        'input'        => 'text',
                        'required'     => false,
                        'visible'      => true,
                        'user_defined' => true,
                        'position'     => 999,
                        'system'       => 0,
                    ]
                );

                $attribute = $customerSetup->getEavConfig()
                    ->getAttribute(Customer::ENTITY, self::EXTERNAL_CLIENT_NUMBER)
                    ->addData([
                        'attribute_set_id'   => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms'      => ['adminhtml_customer'],
                    ]);

                $attribute->save();
            }
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            /* sales_order */
            $connection = $setup->getConnection();
            if ($connection->tableColumnExists(self::SALES_ORDER, self::CLIENT_NUMBER) === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable(self::SALES_ORDER),
                    self::CLIENT_NUMBER,
                    [
                        'type'     => Table::TYPE_TEXT,
                        'nullable' => true,
                        'length'   => '255',
                        'comment'  => 'Client Number'
                    ]
                );
            }

            if ($connection->tableColumnExists(self::SALES_ORDER, self::EXTERNAL_CLIENT_NUMBER) === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable(self::SALES_ORDER),
                    self::EXTERNAL_CLIENT_NUMBER,
                    [
                        'type'     => Table::TYPE_TEXT,
                        'nullable' => true,
                        'length'   => '255',
                        'comment'  => 'External Client Number'
                    ]
                );
            }

            /* sales_order_grid */
            if ($connection->tableColumnExists(self::SALES_ORDER_GRID, self::CLIENT_NUMBER) === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable(self::SALES_ORDER_GRID),
                    self::CLIENT_NUMBER,
                    [
                        'type'     => Table::TYPE_TEXT,
                        'nullable' => true,
                        'length'   => '255',
                        'comment'  => 'Client Number'
                    ]
                );
            }

            if ($connection->tableColumnExists(self::SALES_ORDER_GRID, self::EXTERNAL_CLIENT_NUMBER) === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable(self::SALES_ORDER_GRID),
                    self::EXTERNAL_CLIENT_NUMBER,
                    [
                        'type'     => Table::TYPE_TEXT,
                        'nullable' => true,
                        'length'   => '255',
                        'comment'  => 'External Client Number'
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}

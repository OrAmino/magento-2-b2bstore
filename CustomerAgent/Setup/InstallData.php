<?php

namespace Orienteed\CustomerAgent\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Orienteed\CustomerAgent\Model\Customer\Attribute\Source\AgentList;

class InstallData implements InstallDataInterface
{
    const CUSTOMER_AGENT = "customer_agent";

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
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
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            if (!$customerSetup->getAttributeId(Customer::ENTITY, self::CUSTOMER_AGENT)) {
                $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
                $attributeSetId = $customerEntity->getDefaultAttributeSetId();

                /** @var AttributeSet $attributeSet */
                $attributeSet = $this->attributeSetFactory->create();
                $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

                $customerSetup->addAttribute(Customer::ENTITY, self::CUSTOMER_AGENT, [
                    'type'               => 'int',
                    'label'              => 'Customer Agent',
                    'input'              => 'select',
                    'required'           => false,
                    'visible'            => true,
                    'user_defined'       => true,
                    'sort_order'         => 1000,
                    'position'           => 1000,
                    'system'             => 0,
                    'is_used_in_grid'    => 0,
                    'is_visible_in_grid' => 0,
                    'global'             => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'source'             => AgentList::class,
                ]);

                $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::CUSTOMER_AGENT)
                    ->addData([
                        'attribute_set_id' => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms' => ['adminhtml_customer'],
                    ]);

                $attribute->save();
            }
        }
        $setup->endSetup();
    }
}

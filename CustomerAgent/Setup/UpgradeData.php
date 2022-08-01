<?php

namespace Orienteed\CustomerAgent\Setup;

use Magento\Framework\Setup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;
use Orienteed\CustomerAgent\Model\Customer\Attribute\Source\AgentList;

class UpgradeData implements Setup\UpgradeDataInterface
{
    const CUSTOMER_AGENT = "customer_agent";

    /**
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * __construct
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(
        Setup\ModuleDataSetupInterface $setup,
        Setup\ModuleContextInterface $moduleContext
    ) {
        $setup->startSetup();
        if (version_compare($moduleContext->getVersion(), '1.0.2', '<')) {
            $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
            if ($eavSetup->getAttributeId(Customer::ENTITY, self::CUSTOMER_AGENT)) {
                $eavSetup->updateAttribute(
                    Customer::ENTITY,
                    self::CUSTOMER_AGENT,
                    'is_used_in_grid',
                    1
                );
                $eavSetup->updateAttribute(
                    Customer::ENTITY,
                    self::CUSTOMER_AGENT,
                    'is_visible_in_grid',
                    1
                );
            }
        }

        if (version_compare($moduleContext->getVersion(), '1.0.3', '<')) {
            $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
            if ($eavSetup->getAttributeId(Customer::ENTITY, self::CUSTOMER_AGENT)) {
                $eavSetup->updateAttribute(
                    Customer::ENTITY,
                    self::CUSTOMER_AGENT,
                    'source_model',
                    AgentList::class
                );
            }
        }

        $setup->endSetup();
    }
}

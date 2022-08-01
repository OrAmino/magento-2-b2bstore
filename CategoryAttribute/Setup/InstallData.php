<?php

namespace Orienteed\CategoryAttribute\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Category;

class InstallData implements InstallDataInterface
{

    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            Category::ENTITY,
            'category_icon',
            [
                'type'       => 'varchar',
                'label'      => 'Category Icon',
                'input'      => 'text',
                'sort_order'   => 100,
                'source'       => ScopedAttributeInterface::SCOPE_STORE,
                'global'       => 1,
                'visible'      => true,
                'required'     => false,
                'user_defined' => false,
                'default'      => null,
                'group'        => 'Content',
                'backend'      => ''
            ]
        );
    }
}

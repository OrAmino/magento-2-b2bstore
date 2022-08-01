<?php

namespace Orienteed\CustomerAgent\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    const MAGEPLAZA_REQUESTFORQUOTE_QUOTE = "mageplaza_requestforquote_quote";
    const AGENT_ID = "agent_id";

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $connection = $setup->getConnection();
            if ($connection->tableColumnExists(self::MAGEPLAZA_REQUESTFORQUOTE_QUOTE, self::AGENT_ID) === false) {
                $connection->addColumn(
                    $setup->getTable(self::MAGEPLAZA_REQUESTFORQUOTE_QUOTE),
                    self::AGENT_ID,
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'length'   => '10',
                        'nullable' => true,
                        'default'  => 0,
                        'comment'  => 'Agent Id'
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}

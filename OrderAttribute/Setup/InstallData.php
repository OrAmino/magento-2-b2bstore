<?php

namespace Orienteed\OrderAttribute\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallData implements InstallDataInterface
{
    const COMMENT_COLUMN        = "comment";
    const EXTERNAL_ORDER_NUMBER = "external_order_number";
    const SALES_ORDER           = "sales_order";
    const SALES_ORDER_GRID      = "sales_order_grid";
    const QUOTE_TABLE           = "quote";

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $setup->getConnection();

        /* quote */
        if ($connection->tableColumnExists(self::QUOTE_TABLE, self::COMMENT_COLUMN) === false) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_TABLE),
                self::COMMENT_COLUMN,
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length'   => '255',
                    'comment'  => 'Comment'
                ]
            );
        }
        if ($connection->tableColumnExists(self::QUOTE_TABLE, self::EXTERNAL_ORDER_NUMBER) === false) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_TABLE),
                self::EXTERNAL_ORDER_NUMBER,
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length'   => '255',
                    'comment'  => 'External Order Number'
                ]
            );
        }

        /* sales_order */
        if ($connection->tableColumnExists(self::SALES_ORDER, self::COMMENT_COLUMN) === false) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::SALES_ORDER),
                self::COMMENT_COLUMN,
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length'   => '255',
                    'comment'  => 'Comment'
                ]
            );
        }

        if ($connection->tableColumnExists(self::SALES_ORDER, self::EXTERNAL_ORDER_NUMBER) === false) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::SALES_ORDER),
                self::EXTERNAL_ORDER_NUMBER,
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length'   => '255',
                    'comment'  => 'External Order Number'
                ]
            );
        }

        /* sales_order_grid */
        if ($connection->tableColumnExists(self::SALES_ORDER_GRID, self::COMMENT_COLUMN) === false) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::SALES_ORDER_GRID),
                self::COMMENT_COLUMN,
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length'   => '255',
                    'comment'  => 'Comment'
                ]
            );
        }

        if ($connection->tableColumnExists(self::SALES_ORDER_GRID, self::EXTERNAL_ORDER_NUMBER) === false) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::SALES_ORDER_GRID),
                self::EXTERNAL_ORDER_NUMBER,
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length'   => '255',
                    'comment'  => 'External Order Number'
                ]
            );
        }

        $installer->endSetup();
    }
}

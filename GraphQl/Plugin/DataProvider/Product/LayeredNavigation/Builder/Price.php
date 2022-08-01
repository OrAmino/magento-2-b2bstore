<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Plugin\DataProvider\Product\LayeredNavigation\Builder;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\BucketInterface;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Formatter\LayerFormatter;
use Magento\Framework\App\ResourceConnection;

/**
 * @inheritdoc
 */
class Price extends \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Price
{
    const PRICE_ATTRIBUTE_CODE = 'price';

    /**
     * @var string
     */
    private const PRICE_BUCKET = 'price_bucket';

    /**
     * @var LayerFormatter
     */
    private $layerFormatter;

    /**
     * @var array
     */
    private static $bucketMap = [
        self::PRICE_BUCKET => [
            'request_name' => 'price',
            'label' => 'Price'

        ],
    ];

    /**
     * @param LayerFormatter $layerFormatter
     */
    public function __construct(
        LayerFormatter $layerFormatter,
        ResourceConnection $resourceConnection
    ) {
        $this->layerFormatter = $layerFormatter;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($layerFormatter);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array
    {
        $bucket = $aggregation->getBucket(self::PRICE_BUCKET);
        if ($this->isBucketEmpty($bucket)) {
            return [];
        }

        $result = $this->layerFormatter->buildLayer(
            self::$bucketMap[self::PRICE_BUCKET]['label'],
            \count($bucket->getValues()),
            self::$bucketMap[self::PRICE_BUCKET]['request_name'],
            $this->getPricePosition()
        );

        foreach ($bucket->getValues() as $value) {
            $metrics = $value->getMetrics();
            $result['options'][] = $this->layerFormatter->buildItem(
                \str_replace('_', '-', $metrics['value']),
                $metrics['value'],
                $metrics['count']
            );
        }

        return [self::PRICE_BUCKET => $result];
    }

    /**
     * Check that bucket contains data
     *
     * @param BucketInterface|null $bucket
     * @return bool
     */
    private function isBucketEmpty(?BucketInterface $bucket): bool
    {
        return null === $bucket || !$bucket->getValues();
    }

    private function getPricePosition()
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                ['a' => $this->resourceConnection->getTableName('eav_attribute')],
                [
                    'attribute_id' => 'a.attribute_id',
                    'attribute_code' => 'a.attribute_code',
                    'position' => 'attribute_configuration.position'
                ]
            )
            ->joinLeft(
                ['attribute_configuration' => $this->resourceConnection->getTableName('catalog_eav_attribute')],
                'a.attribute_id = attribute_configuration.attribute_id',
                []
            )->where(
                'a.attribute_code = ?',
                self::PRICE_ATTRIBUTE_CODE
            );

        $statement = $this->resourceConnection->getConnection()->query($select);

        $result = $statement->fetch();

        if (isset($result['position'])) {
            return $result['position'];
        }

        return 0;
    }
}

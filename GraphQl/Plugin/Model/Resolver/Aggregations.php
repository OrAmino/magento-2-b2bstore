<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Plugin\Model\Resolver;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Layered navigation filters resolver, used for GraphQL request processing.
 */
class Aggregations extends \Magento\CatalogGraphQl\Model\Resolver\Aggregations
{
    /**
     * @var Layer\DataProvider\Filters
     */
    private $filtersDataProvider;

    /**
     * @var LayerBuilder
     */
    private $layerBuilder;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters $filtersDataProvider
     * @param LayerBuilder $layerBuilder
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters $filtersDataProvider,
        LayerBuilder $layerBuilder,
        PriceCurrency $priceCurrency = null
    ) {
        $this->filtersDataProvider = $filtersDataProvider;
        $this->layerBuilder = $layerBuilder;
        $this->priceCurrency = $priceCurrency ?: ObjectManager::getInstance()->get(PriceCurrency::class);
        parent::__construct($filtersDataProvider, $layerBuilder);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (!isset($value['layer_type']) || !isset($value['search_result'])) {
            return null;
        }

        $aggregations = $value['search_result']->getSearchAggregation();

        if ($aggregations) {
            /** @var StoreInterface $store */
            $store = $context->getExtensionAttributes()->getStore();
            $storeId = (int)$store->getId();
            $results = $this->layerBuilder->build($aggregations, $storeId);

            if (isset($results['price_bucket'])) {
                foreach ($results['price_bucket']['options'] as &$value) {
                    list($from, $to) = explode('-', $value['label']);
                    $newLabel = $this->priceCurrency->convertAndRound($from)
                        . '-'
                        . $this->priceCurrency->convertAndRound($to);
                    $value['label'] = $newLabel;
                    $value['value'] = str_replace('-', '_', $newLabel);
                }
            }

            /** Custom Start */
            array_multisort(array_column($results, 'position'), SORT_ASC, $results);
            /** Custom End */

            return $results;
        } else {
            return [];
        }
    }
}

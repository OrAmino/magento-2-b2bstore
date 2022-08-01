<?php

namespace Orienteed\GraphQl\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolve data for product Brand name
 */
class OrParentSku implements ResolverInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $resourceProduct;
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $resourceConfigurable;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable
    ) {
        $this->resourceProduct = $resourceProduct;
        $this->resourceConfigurable = $resourceConfigurable;
    }

    public function getParentSku($childSku)
    {
        $childId = $this->resourceProduct->getIdBySku($childSku);
        if ($childId) {
            $parentIds = $this->resourceConfigurable->getParentIdsByChild($childId);
            if (!empty($parentIds)) {
                $skus = $this->resourceProduct->getProductsSku($parentIds);
                return $skus[0]['sku'];
            }
        }
        return null;
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
        if (!isset($value['sku'])) {
            throw new LocalizedException(__('"sku" value should be specified'));
        }

        $sku = $value['sku'];

        return $this->getParentSku($sku);
    }
}

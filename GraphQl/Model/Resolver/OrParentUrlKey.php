<?php

namespace Orienteed\GraphQl\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Resolve data for product Brand name
 */
class OrParentUrlKey implements ResolverInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $resourceProduct;
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $resourceConfigurable;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->resourceProduct = $resourceProduct;
        $this->resourceConfigurable = $resourceConfigurable;
        $this->productRepository = $productRepository;
    }

    public function getProductUrl($productSku)
    {
        $product = $this->productRepository->get($productSku);
        return $product->getUrlKey();
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
        $parentSku = $this->getParentSku($sku) ? $this->getParentSku($sku) : $sku;

        return $this->getProductUrl($parentSku);
    }
}

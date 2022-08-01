<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Mageplaza\RequestForQuote\Model\ResourceModel\Quote\Item\CollectionFactory;

class MpQuoteItemDiscount implements ResolverInterface
{
    const CONFIGURABLE = "configurable";

    /**
     * @var \Mageplaza\RequestForQuote\Helper\Data
     */
    private $helperData;

    /**
     * @var CollectionFactory
     */
    private $_itemCollection;

    /**
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        \Mageplaza\RequestForQuote\Helper\Data $helperData,
        CollectionFactory $itemCollection
    ) {
        $this->helperData      = $helperData;
        $this->_itemCollection = $itemCollection;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $item = $value['model'];

        $price = $item->getBaseRequestPrice();
        if ($item->getPrice() > 0) {
            $price = $item->getPrice();
        }

        /* new code for resolving discount code 0 */
        if ($item->getProductType() == self::CONFIGURABLE) {
            $itemCollection = $this->_itemCollection->create();
            $itemCollection->addFieldToFilter('quote_id', $item->getQuoteId());
            $itemCollection->addFieldToFilter('item_id', $item->getId());

            if (count($itemCollection) > 0) {
                foreach ($itemCollection as $_item) {
                    $price = $_item->getPrice();
                }
            }
        }

        $subtotalRequest  = $item->getBaseRequestPrice() * $item->getQty();
        $subtotalOriginal = $price * $item->getQty();
        $discount         = round(100 - ($subtotalRequest * 100 / $subtotalOriginal), 2);
        $priceItem        = $this->helperData->formatPrice($subtotalOriginal - $subtotalRequest);

        return $this->helperData->discountHtml($discount, $priceItem);
    }
}

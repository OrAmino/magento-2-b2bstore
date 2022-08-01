<?php

namespace Orienteed\GraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CartTotal implements ResolverInterface
{
    public function __construct(
        \Mageplaza\SaveCart\Helper\Data $helper,
        \Mageplaza\SaveCart\Model\ResourceModel\CartItem\CollectionFactory $cartItemCollection
    ) {
        $this->helper = $helper;
        $this->cartItemCollection = $cartItemCollection;
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
        $currencyCode = $context->getExtensionAttributes()->getStore()->getCurrentCurrency()->getCode();

        $total = 0;
        try {
            $cartItems = $this->cartItemCollection->create()
                ->addFieldToFilter('cart_id', $value['cart_id']);
            foreach ($cartItems as $item) {
                $total += $this->helper->getSubtotal($item, $value['store_id']);
            }
        } catch (\Exception $e) {
            throw new GraphQlInputException(
                __('Can\'t assign cart to store in different website.')
            );
        }

        $response = [
            'currency' => $currencyCode,
            'value' => $total
        ];

        return $response;
    }
}

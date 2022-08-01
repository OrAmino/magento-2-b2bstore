<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Model\Resolver\Query;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class GetCarts
{
    public function afterResolve(\Mageplaza\SaveCartGraphQl\Model\Resolver\Query\GetCarts $subject, $result)
    {
        $items = $result['items'];
        unset($result['items']);

        if (count($items)) {
            foreach ($items as $key => $item) {
                $result['items'][$key] = [
                    'cart_id' => $item->getCartId(),
                    'store_id' => $item->getStoreId(),
                    'created_at' => $item->getCreatedAt(),
                    'cart_name' => $item->getCartName(),
                    'customer_id' => $item->getCustomerId(),
                    'share_url' => $item->getShareUrl(),
                    'token' => $item->getToken(),
                    'items' => $item->getItems(),
                    'description' => $item->getDescription()
                ];
            }
        } else {
            $result['items'] = [];
        }

        return $result;
    }
}

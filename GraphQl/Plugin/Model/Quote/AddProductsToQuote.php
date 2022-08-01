<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Plugin\Model\Quote;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class AddProductsToQuote
{
    private $addProductToQuote;

    /**
     * AddProductsToQuote constructor.
     *
     * @param AddSimpleProductToQuote $addProductToQuote
     */
    public function __construct(
        AddSimpleProductToQuote $addProductToQuote
    ) {
        $this->addProductToQuote = $addProductToQuote;
    }

    /**
     * @param $customerId
     * @param array $cartItems
     *
     * @throws GraphQlInputException
     */
    public function execute($customerId, array $cartItems, $quote): void
    {
        foreach ($cartItems as $cartItemData) {
            $this->addProductToQuote->execute($customerId, $cartItemData, $quote);
        }
    }
}

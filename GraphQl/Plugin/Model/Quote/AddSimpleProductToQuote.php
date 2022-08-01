<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Plugin\Model\Quote;

use Exception;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestBuilder;
use Mageplaza\RequestForQuote\Model\Api\QuoteRepository;

class AddSimpleProductToQuote
{
    /**
     * @var BuyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var CartItemInterfaceFactory
     */
    private $cartItemFactory;

    /**
     * AddSimpleProductToQuote constructor.
     *
     * @param BuyRequestBuilder $buyRequestBuilder
     * @param QuoteRepository $quoteRepository
     * @param CartItemInterfaceFactory $cartItemFactory
     */
    public function __construct(
        BuyRequestBuilder $buyRequestBuilder,
        QuoteRepository $quoteRepository,
        CartItemInterfaceFactory $cartItemFactory
    ) {
        $this->buyRequestBuilder = $buyRequestBuilder;
        $this->quoteRepository   = $quoteRepository;
        $this->cartItemFactory   = $cartItemFactory;
    }

    /**
     * @param $customerId
     * @param array $cartItemData
     *
     * @throws GraphQlInputException
     */
    public function execute($customerId, array $cartItemData, $quote): void
    {
        $sku = $this->extractSku($cartItemData);
        $cartItemData['model'] = $quote;
        try {
            $quoteItemData        = $this->buyRequestBuilder->build($cartItemData);
            $quoteItemData['sku'] = $sku;
            $quoteItem            = $this->cartItemFactory->create(['data' => $quoteItemData->getData()]);
            $this->quoteRepository->addItem($customerId, $quoteItem);
        } catch (Exception $e) {
            throw new GraphQlInputException(
                __(
                    'Could not add the product with SKU %sku to the shopping cart: %message',
                    ['sku' => $sku, 'message' => $e->getMessage()]
                )
            );
        }
    }

    /**
     * Extract SKU from cart item data
     *
     * @param array $cartItemData
     *
     * @return string
     * @throws GraphQlInputException
     */
    private function extractSku(array $cartItemData): string
    {
        // Need to keep this for configurable product and backward compatibility.
        if (!empty($cartItemData['parent_sku'])) {
            return (string)$cartItemData['parent_sku'];
        }
        if (empty($cartItemData['data']['sku'])) {
            throw new GraphQlInputException(__('Missed "sku" in cart item data'));
        }

        return (string)$cartItemData['data']['sku'];
    }
}

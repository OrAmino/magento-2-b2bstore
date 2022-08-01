<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Plugin\Model\Resolver;

use Exception;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Mageplaza\RequestForQuote\Api\Data\UpdateItemInterfaceFactory;
use Mageplaza\RequestForQuote\Model\Api\QuoteRepository;

class Update extends \Mageplaza\RequestForQuoteGraphQl\Model\Resolver\Update
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var UpdateItemInterfaceFactory
     */
    private $updateItemFactory;

    /**
     * Update constructor.
     *
     * @param QuoteRepository $quoteRepository
     * @param UpdateItemInterfaceFactory $updateItemFactory
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        UpdateItemInterfaceFactory $updateItemFactory
    ) {
        $this->quoteRepository   = $quoteRepository;
        $this->updateItemFactory = $updateItemFactory;
        parent::__construct($quoteRepository, $updateItemFactory);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $currentUserId = $context->getUserId();

        /** @var ContextInterface $context */
        if ($context->getExtensionAttributes()->getIsCustomer() === false) {
            throw new AuthorizationException(__('The request is allowed for logged in customer'));
        }

        $quoteId = $args['input']['quote_id'] ?? null;
        $items   = [];
        foreach ($args['input']['items'] as $item) {
            $items[] = $this->updateItemFactory->create(['data' => $item]);
        }

        try {

            $this->quoteRepository->update($currentUserId, $items, $quoteId);
            $quote = $this->quoteRepository->getInactiveQuoteCart($currentUserId);
        } catch (Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return [
            'quote' => array_merge($quote->getData(), [
                'model' => $quote
            ])
        ];
    }
}

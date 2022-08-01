<?php

declare(strict_types=1);

namespace Orienteed\OrderAttribute\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * @package Orienteed\OrderAttribute\Model\Resolver
 * 
 * CustomAttributeQuoteSave
 */
class CustomAttributeQuoteSave implements ResolverInterface
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $_maskedQuoteIdToQuoteId;

    /**
     * @var CartRepositoryInterface
     */
    private $_quoteRepository;

    /**
     * __construct
     *
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->_maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->_quoteRepository        = $quoteRepository;
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
        $maskedId = $args['masked_id'];
        try {
            $cartId = $this->_maskedQuoteIdToQuoteId->execute($maskedId);
            $quote  = $this->getQuoteById($cartId);
            if ($quote) {
                if (isset($args['comment'])) {
                    $this->saveComment($args['comment'], $quote);
                }
                if (isset($args['external_order_number'])) {
                    $this->saveExternalOrderNumber($args['external_order_number'], $quote);
                }
                $this->_quoteRepository->save($quote);

                return ['status' => true, 'message' => __("Additional information saved.")];
            }
        } catch (NoSuchEntityException $exception) {
            throw new LocalizedException(
                __('Could not find a cart with ID "%masked_id"', ['masked_id' => $maskedId])
            );
        }

        return ['status' => false, 'message' => __("Something Went Wrong.")];
    }

    /**
     * saveComment
     *
     * @param [string] $comment
     * @param [object] $quote
     * @return object
     */
    private function saveComment($comment, $quote)
    {
        $quote->setComment($comment);
    }

    /**
     * saveExternalOrderNumber
     *
     * @param [string] $externalOrderNumber
     * @param [object] $quote
     * @return object
     */
    private function saveExternalOrderNumber($externalOrderNumber, $quote)
    {
        $quote->setExternalOrderNumber($externalOrderNumber);
    }

    /**
     * getQuoteById
     *
     * @param integer $quoteId
     * @return CartInterface|null
     */
    private function getQuoteById(int $quoteId): ?CartInterface
    {
        $quote = null;
        try {
            $quote = $this->_quoteRepository->get($quoteId);
        } catch (NoSuchEntityException $exception) {
            throw new LocalizedException(
                __('Quote does not exist', ['error' => $exception->getMessage()])
            );
        }
        return $quote;
    }
}

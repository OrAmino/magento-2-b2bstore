<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Plugin\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Mageplaza\RequestForQuote\Model\Api\QuoteRepository;
use Mageplaza\RequestForQuote\Model\CartQuote;
use Magento\Framework\Exception\AuthorizationException;

class AddSimpleProductsToQuote extends \Mageplaza\RequestForQuoteGraphQl\Model\Resolver\AddSimpleProductsToQuote
{

    /**
     * @var AddProductsToQuote
     */
    private $addProductsToQuote;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var CartQuote
     */
    private $cartQuote;

    /**
     * AddSimpleProductsToQuote constructor.
     *
     * @param AddProductsToQuote $addProductsToQuote
     * @param GetCustomer $getCustomer
     * @param QuoteRepository $quoteRepository
     * @param CartQuote $cartQuote
     */
    public function __construct(
        \Mageplaza\RequestForQuoteGraphQl\Model\Quote\AddProductsToQuote $_addProductsToQuote,
        GetCustomer $getCustomer,
        QuoteRepository $quoteRepository,
        CartQuote $cartQuote,
        \Orienteed\GraphQl\Plugin\Model\Quote\AddProductsToQuote $addProductsToQuote
    ) {
        $this->addProductsToQuote = $addProductsToQuote;
        $this->getCustomer        = $getCustomer;
        $this->quoteRepository    = $quoteRepository;
        $this->cartQuote = $cartQuote;
        parent::__construct($_addProductsToQuote, $getCustomer, $quoteRepository, $cartQuote);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var ContextInterface $context */
        if ($context->getExtensionAttributes()->getIsCustomer() === false) {
            throw new AuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (
            empty($args['input']['cart_items'])
            || !is_array($args['input']['cart_items'])
        ) {
            throw new GraphQlInputException(__('Required parameter "cart_items" is missing'));
        }
        $cartItems  = $args['input']['cart_items'];
        $customer   = $this->getCustomer->execute($context);
        $customerId = $customer->getId();
        $quote = $this->quoteRepository->getInactiveQuoteCart($customerId);
        $this->addProductsToQuote->execute($customerId, $cartItems, $quote);
        $quote = $this->quoteRepository->getInactiveQuoteCart($customerId);
        $this->cartQuote->collectQuoteById($quote->getId());
        $quote->load($quote->getId());

        return [
            'quote' => array_merge($quote->getData(), [
                'model' => $quote
            ])
        ];
    }
}

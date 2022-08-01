<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

class CustomerQuoteId implements ResolverInterface
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        GetCustomer $getCustomer,
        \Mageplaza\RequestForQuote\Model\Api\QuoteRepository $quoteRepository
    ) {
        $this->getCustomer = $getCustomer;
        $this->quoteRepository = $quoteRepository;
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
        $customer = $this->getCustomer->execute($context);
        $quote    = $this->quoteRepository->getInactiveQuoteCart($customer->getId());
        $quoteId  = 0;
        if ($quote->getId()) {
            $quoteId = $quote->getId();
        }

        return $quoteId;
    }
}

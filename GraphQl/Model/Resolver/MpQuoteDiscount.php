<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

class MpQuoteDiscount implements ResolverInterface
{
    /**
     * @var \Mageplaza\RequestForQuote\Helper\Data
     */
    private $helperData;

    /**
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        \Mageplaza\RequestForQuote\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
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

        $quote = $value['model'];

        return $this->helperData->calculateDiscountForQuote($quote);
    }
}

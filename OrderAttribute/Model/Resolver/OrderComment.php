<?php

declare(strict_types=1);

namespace Orienteed\OrderAttribute\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;

/**
 * @inheritdoc
 */
class OrderComment implements ResolverInterface
{
    /**
     * @var CollectionFactoryInterface
     */
    private $_collectionFactory;

    /**
     * __construct
     *
     * @param CollectionFactoryInterface $collectionFactory
     */
    public function __construct(
        CollectionFactoryInterface $collectionFactory
    ) {
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
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

        $orders  = $this->_collectionFactory->create($context->getUserId());
        $comment = "";

        /** @var Order $order */
        foreach ($orders as $order) {
            if ($order->getComment()) {
                $comment = $order->getComment();
            }
        }

        return $comment;
    }
}

<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Plugin\Model\Resolver\Query;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Api\SaveCartRepositoryInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;

class GetCarts extends \Mageplaza\SaveCartGraphQl\Model\Resolver\Query\GetCarts
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SaveCartRepositoryInterface
     */
    private $saveCartRepository;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * GetCarts constructor.
     *
     * @param Data $helperData
     * @param GetCustomer $getCustomer
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SaveCartRepositoryInterface $saveCartRepository
     */
    public function __construct(
        Data $helperData,
        GetCustomer $getCustomer,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SaveCartRepositoryInterface $saveCartRepository,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->saveCartRepository    = $saveCartRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;

        parent::__construct($helperData, $getCustomer, $searchCriteriaBuilder, $saveCartRepository);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        parent::resolve($field, $context, $info, $value, $args);

        $customer = $this->getCustomer->execute($context);
        $this->validate($args);

        $sortOrder = $this->sortOrderBuilder->setField('cart_id')->setDirection('DESC')->create();
        $searchCriteria = $this->searchCriteriaBuilder->build('mp_save_cart_carts', $args);
        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);
        $searchCriteria->setSortOrders([$sortOrder]);
        $searchResult = $this->saveCartRepository->getList((int)$customer->getId(), $searchCriteria);

        return $this->getResult($searchResult, $args);
    }
}

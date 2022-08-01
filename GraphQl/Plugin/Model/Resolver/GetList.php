<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Plugin\Model\Resolver;

use Exception;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Mageplaza\RequestForQuote\Model\Api\QuoteRepository;

class GetList extends \Mageplaza\RequestForQuoteGraphQl\Model\Resolver\GetList
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * GetCarts constructor.
     *
     * @param Data $helperData
     * @param GetCustomer $getCustomer
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SaveCartRepositoryInterface $saveCartRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
    ) {
        $this->quoteRepository       = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        parent::__construct($quoteRepository, $searchCriteriaBuilder);
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

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDirection('DESC')->create();

        $searchCriteria = $this->searchCriteriaBuilder->build('quotes', $args);
        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);
        $searchCriteria->setSortOrders([$sortOrder]);

        try {
            $searchResult = $this->quoteRepository->getList($currentUserId, $searchCriteria);
            $pageInfo     = $this->getPageInfo($searchResult, $searchCriteria, $args);
            $items        = [];
            foreach ($searchResult->getItems() as $item) {
                $items[$item->getId()]          = $item->getData();
                $items[$item->getId()]['model'] = $item;
            }

            return [
                'total_count' => $searchResult->getTotalCount(),
                'items'       => $items,
                'page_info'   => $pageInfo
            ];
        } catch (Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}

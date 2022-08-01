<?php

namespace Orienteed\GraphQl\Plugin\Api;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Mageplaza\SaveCart\Api\Data\CartSearchResultInterface;
use Mageplaza\SaveCart\Api\SaveCartRepositoryInterface;
use Mageplaza\SaveCart\Helper\Data;
use Mageplaza\SaveCart\Model\Cart;
use Mageplaza\SaveCart\Model\CartFactory;
use Mageplaza\SaveCart\Model\CartItem;
use Mageplaza\SaveCart\Model\CartItemFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Cart\Collection;
use Mageplaza\SaveCart\Model\ResourceModel\Cart\CollectionFactory;
use Mageplaza\SaveCart\Model\ResourceModel\CartItem\CollectionFactory as CartItemCollectionFactory;


class SaveCartRepository extends \Mageplaza\SaveCart\Model\Api\SaveCartRepository
{
    protected $qrientedGraphQlHelperData;

    /**
     * SaveCartRepository constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param UrlInterface $url
     * @param CollectionFactory $cartCollectionFactory
     * @param CartItemCollectionFactory $cartItemCollectionFactory
     * @param Data $helperData
     * @param CartFactory $saveCartFactory
     * @param State $state
     * @param ProductRepository $productRepository
     * @param CheckoutCart $checkoutCart
     * @param CartRepositoryInterface $cartRepository
     * @param RequestInterface $request
     * @param CartItemFactory $saveCartItemFactory
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory,
        UrlInterface $url,
        CollectionFactory $cartCollectionFactory,
        CartItemCollectionFactory $cartItemCollectionFactory,
        Data $helperData,
        CartFactory $saveCartFactory,
        State $state,
        ProductRepository $productRepository,
        CheckoutCart $checkoutCart,
        CartRepositoryInterface $cartRepository,
        RequestInterface $request,
        CartItemFactory $saveCartItemFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        \Orienteed\GraphQl\Helper\Data $qrientedGraphQlHelperData
    ) {
        parent::__construct(
            $searchCriteriaBuilder,
            $collectionProcessor,
            $searchResultsFactory,
            $url,
            $cartCollectionFactory,
            $cartItemCollectionFactory,
            $helperData,
            $saveCartFactory,
            $state,
            $productRepository,
            $checkoutCart,
            $cartRepository,
            $request,
            $saveCartItemFactory,
            $quoteIdMaskFactory
        );
        $this->qrientedGraphQlHelperData = $qrientedGraphQlHelperData;
    }

    /**
     * @param int $customerId
     * @param SearchCriteriaInterface|null $searchCriteria
     *
     * @return SearchResultsInterface|CartSearchResultInterface
     * @throws LocalizedException
     */
    public function getList($customerId, SearchCriteriaInterface $searchCriteria = null)
    {
        $this->helperData->checkEnabled();
        if ($searchCriteria === null) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }

        $collection = $this->cartCollectionFactory->create()->addFieldToFilter('customer_id', $customerId);

        $this->collectionProcessor->process($searchCriteria, $collection);

        foreach ($collection->getItems() as $cart) {
            $items = $this->cartItemCollectionFactory->create()
                ->addFieldToFilter('cart_id', $cart->getCartId())->getItems();

            $storeId = $cart->getStoreId();
            if ($this->helperData->allowShare()) {
                $frontendUrl = $this->qrientedGraphQlHelperData->getStoreFrontUrl($storeId);
                $cart->setShareUrl($frontendUrl . 'mpsavecart/cart/share/id/' . $cart->getToken());
            }

            foreach ($items as $cartItem) {
                $cartItem->setProductName($this->helperData->getProductName($cartItem, $storeId));
                $cartItem->setSku($this->helperData->getSku($cartItem, $storeId));
                $cartItem->setImage($this->helperData->getImage($cartItem, $storeId));
                $cartItem->setPrice($this->helperData->getPrice($cartItem, $storeId, true, false));
                $cartItem->setSubtotalConverted(
                    $this->helperData->getSubtotalConverted($cartItem, $storeId, true, false)
                );
            }

            $cart->setItems($items);
        }

        /** @var SearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param int $customerId
     * @param string $token
     *
     * @return Cart
     * @throws InputException
     * @throws LocalizedException
     */
    public function get($customerId, $token)
    {
        $this->helperData->checkEnabled();
        if (!$token) {
            throw new InputException(__('Token is required'));
        }
        $saveCart = $this->saveCartFactory->create()->load($token, 'token');

        if (!($cartId = $saveCart->getId()) || (int)$saveCart->getCustomerId() !== $customerId) {
            throw  new LocalizedException(__('Cart does not exist'));
        }
        if ($this->helperData->allowShare()) {
            $storeId = $cart->getStoreId();
            $frontendUrl = $this->qrientedGraphQlHelperData->getStoreFrontUrl($storeId);
            $cart->setShareUrl($frontendUrl . 'mpsavecart/cart/share/id/' . $cart->getToken());
        }
        $items = $this->state->emulateAreaCode(Area::AREA_FRONTEND, [$this, 'getCartItems'], [$cartId]);
        $saveCart->setItems($items);

        return $saveCart;
    }
}

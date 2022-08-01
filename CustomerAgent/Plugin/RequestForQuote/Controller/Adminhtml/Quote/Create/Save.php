<?php

/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RequestForQuote
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Orienteed\CustomerAgent\Plugin\RequestForQuote\Controller\Adminhtml\Quote\Create;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory;
use Mageplaza\RequestForQuote\Controller\Adminhtml\Requests;
use Mageplaza\RequestForQuote\Helper\Email;
use Mageplaza\RequestForQuote\Helper\Image;
use Mageplaza\RequestForQuote\Model\CartQuote;
use Mageplaza\RequestForQuote\Model\Quote\Item\Option;
use Mageplaza\RequestForQuote\Model\Quote\Item\OptionFactory;
use Mageplaza\RequestForQuote\Model\Quote\ItemFactory;
use Mageplaza\RequestForQuote\Model\Quote\ReplyFactory;
use Mageplaza\RequestForQuote\Model\QuoteFactory;
use Orienteed\CustomerAgent\Helper\Data as OrienteedHelperData;

/**
 * Class Save
 * @package Mageplaza\RequestForQuote\Controller\Adminhtml\Quote\Create
 */
class Save extends \Mageplaza\RequestForQuote\Controller\Adminhtml\Quote\Create\Save
{
    protected $_orienteedHelperData;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param Product $productHelper
     * @param Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param CartQuote $cartQuote
     * @param QuoteFactory $quoteFactory
     * @param ReplyFactory $replyFactory
     * @param ItemFactory $itemFactory
     * @param OptionFactory $optionFactory
     * @param CollectionFactory $optionCollectionFactory
     * @param Email $helperData
     * @param Image $helperImage
     * @param Requests $requests
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Action\Context $context,
        Product $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        CartQuote $cartQuote,
        QuoteFactory $quoteFactory,
        ReplyFactory $replyFactory,
        ItemFactory $itemFactory,
        OptionFactory $optionFactory,
        CollectionFactory $optionCollectionFactory,
        Email $helperData,
        Image $helperImage,
        Requests $requests,
        ProductRepositoryInterface $productRepository,
        \Magento\Backend\Model\Auth\Session $authSession,
        OrienteedHelperData $orienteedHelperData
    ) {
        $this->authSession = $authSession;
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory,
            $cartQuote,
            $quoteFactory,
            $replyFactory,
            $itemFactory,
            $optionFactory,
            $optionCollectionFactory,
            $helperData,
            $helperImage,
            $requests,
            $productRepository
        );
        $this->_orienteedHelperData = $orienteedHelperData;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $quote      = $this->_getSession()->getQuote();
            $quoteItems = $quote->getAllItems();

            if ($quoteItems) {
                $newQuoteData = [];

                foreach ($quote->getData() as $key => $value) {
                    $newQuoteData[$key]         = $value;
                    $newQuoteData['entity_id']  = null;
                    $newQuoteData['is_active']  = 1;
                    $newQuoteData['status']     = 'approved';
                    $newQuoteData['expired_at'] = $this->requests->getExpiredTime();
                    $newQuoteData['agent_id']   = $this->getAgentId();
                }

                $newQuote = $this->quoteFactory->create()->setData($newQuoteData)->save();

                /** @var Item $item */
                foreach ($quoteItems as $item) {
                    if ($item->getProductType() === Type::TYPE_SIMPLE) {
                        $stockQty       = $item->getProduct()->getExtensionAttributes()->getStockItem()->getQty();
                        $currentProduct = $this->productRepository->getById($item->getProduct()->getId());
                        $productStock   = $currentProduct->getExtensionAttributes()->getStockItem();
                        $isBackorders   = $productStock->getBackorders();

                        if ($stockQty < $item->getQty() && !$isBackorders) {
                            throw new LocalizedException(__('The requested qty is not available'));
                        }
                    }

                    $newItemData   = [];
                    $newOptionData = [];
                    foreach ($item->getData() as $key => $value) {
                        $newItemData[$key]                 = $value;
                        $newItemData['item_id']            = null;
                        $newItemData['quote_id']           = $newQuote->getId();
                        $newItemData['price']              = $item->getProduct()->getPrice();

                        /* custom code start for resolving catalog price rule issue */
                        if ($discountedPrice = $this->_orienteedHelperData->getPriceAfterDiscount($item->getProduct())) {
                            if ($discountedPrice > 0) {
                                $newItemData['price'] = $discountedPrice;
                            }
                        }
                        /* custom code ends for resolving catalog price rule issue */
                        $newItemData['base_request_price'] = $item->getData('custom_price') ?: $item->getData('price');
                        if ($item->getParentItemId()) {
                            $newItemData['parent_item_id'] = $this->parentItemId;
                        }
                    }
                    $newItem = $this->itemFactory->create()->setData($newItemData);
                    $newQuote->collect($newItem);

                    if (!$newItem->getParentItemId()) {
                        $this->parentItemId = $newItem->getId();
                    }

                    $options = $this->optionCollectionFactory->create()->addFieldToFilter('item_id', $item->getId());

                    /** @var Option $option */
                    foreach ($options as $option) {
                        foreach ($option->getData() as $key => $value) {
                            $newOptionData[$key]        = $value;
                            $newOptionData['option_id'] = null;
                            $newOptionData['item_id']   = $newItem->getId();
                        }

                        $this->optionFactory->create()->setData($newOptionData)->save();
                    }
                }

                $request = $this->getRequest()->getParams();

                if ((isset($request['reply']['content']) && $request['reply']['content']) ||
                    (isset($request['reply']['files']) && $request['reply']['files'])
                ) {
                    $this->addComment($request['reply'], $newQuote->getId());
                }

                /** Send notify created email to customer */
                if ($this->helperData->getConfigEmail('new_update/is_enable_created')) {
                    $this->helperData->sendEmail(
                        $this->helperData->getCreatedTemplate($newQuote->getStoreId()),
                        $newQuote->getCustomerEmail(),
                        $this->helperData->getTemplateParams($newQuote)
                    );
                }

                $this->messageManager->addSuccessMessage(__('You\'ve created the quote cart.'));
            } else {
                $this->messageManager->addErrorMessage(__('Please specify quote items.'));
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/requests/index');
    }

    private function getAgentId()
    {
        $userId = 1;
        if ($this->authSession->getUser()) {
            $userId = $this->authSession->getUser()->getId();
        }
        return $userId;
    }
}

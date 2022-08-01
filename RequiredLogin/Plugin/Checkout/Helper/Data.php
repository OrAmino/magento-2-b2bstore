<?php

declare(strict_types=1);

namespace Orienteed\RequiredLogin\Plugin\Checkout\Helper;

use Mageplaza\RequestForQuote\Api\Data\UpdateItemInterfaceFactory;
use Mageplaza\RequestForQuote\Model\Api\QuoteRepository;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Checkout\Helper\Data
{
    /**
     * Update constructor.
     *
     * @param QuoteRepository $quoteRepository
     * @param UpdateItemInterfaceFactory $updateItemFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Orienteed\RequiredLogin\Helper\Data $helperData
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_eventManager = $context->getEventManager();
        $this->helperData = $helperData;
        parent::__construct($context, $storeManager, $checkoutSession, $localeDate, $transportBuilder, $inlineTranslation, $priceCurrency);
    }

    public function isAllowedGuestCheckout(\Magento\Quote\Model\Quote $quote, $store = null)
    {
        if ($store === null) {
            $store = $quote->getStoreId();
        }
        $guestCheckout = $this->scopeConfig->isSetFlag(
            \Magento\Checkout\Helper\Data::XML_PATH_GUEST_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($this->helperData->isLoginRequired($store)) {
            $guestCheckout = true;
        }

        if ($guestCheckout == true) {
            $result = new \Magento\Framework\DataObject();
            $result->setIsAllowed($guestCheckout);
            $this->_eventManager->dispatch(
                'checkout_allow_guest',
                ['quote' => $quote, 'store' => $store, 'result' => $result]
            );

            $guestCheckout = $result->getIsAllowed();
        }

        return $guestCheckout;
    }
}

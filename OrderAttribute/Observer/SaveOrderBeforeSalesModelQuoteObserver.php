<?php

namespace Orienteed\OrderAttribute\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\DataObject\Copy;

class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{
    /**
     * @var Copy
     */
    protected $_objectCopyService;

    public function __construct(
        Copy $objectCopyService
    ) {
        $this->_objectCopyService = $objectCopyService;
    }

    /**
     * execute
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $quote = $observer->getEvent()->getData('quote');

        $this->_objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);

        return $this;
    }
}

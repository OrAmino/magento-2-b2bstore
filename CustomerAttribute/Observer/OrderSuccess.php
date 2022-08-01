<?php

namespace Orienteed\CustomerAttribute\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * @package Orienteed\CustomerAttribute\Observer
 * 
 * OrderSuccess
 */
class OrderSuccess implements ObserverInterface
{
    const EXTERNAL_CLIENT_NUMBER  = "external_client_number";
    const CLIENT_NUMBER           = "client_number";

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * __construct
     *
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_customerRepository = $customerRepository;
    }

    /**
     * execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        /* logged in customer */
        if (!$order->getCustomerIsGuest()) {
            if ($customerId = $order->getCustomerId()) {
                $customer = $this->_customerRepository->getById($customerId);
                if ($customer->getCustomAttribute(self::CLIENT_NUMBER)) {
                    $clientNumber = $customer->getCustomAttribute(self::CLIENT_NUMBER)->getValue();
                    $order->setClientNumber($clientNumber);
                }
                if ($customer->getCustomAttribute(self::EXTERNAL_CLIENT_NUMBER)) {
                    $exClientNumber = $customer->getCustomAttribute(self::EXTERNAL_CLIENT_NUMBER)->getValue();
                    $order->setExternalClientNumber($exClientNumber);
                }
            }
        }
    }
}

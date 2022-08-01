<?php

namespace Orienteed\CustomerDiscountGraphql\Plugin\Resolver;

class ProductsPlugin
{
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CustomerGraphQl\Model\Customer\GetCustomer $getCustomer
    ) {
        $this->_customerSession = $customerSession;
        $this->getCustomer = $getCustomer;
    }
    public function beforeResolve(
        \Magento\CatalogGraphQl\Model\Resolver\Products $products,
        $field,
        $context
    ) {
        if (true === $context->getExtensionAttributes()->getIsCustomer()) {
            $customer = $this->getCustomer->execute($context);
            $this->_customerSession->setCustomerData($customer);
        } else {
            $this->_customerSession->logout();
        }
    }
}

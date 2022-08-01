<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Model\Resolver\Query\Webkul;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

class PaymentCreditsystemConfig implements ResolverInterface
{
    /**
     * @var \Webkul\CustomerCreditSystem\Helper\Data
     */
    private $_helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @param \Webkul\CustomerCreditSystem\Model\CreditsystemConfigProviderFactory $configProvider
     */
    public function __construct(
        \Webkul\CustomerCreditSystem\Helper\Data $helper,
        \Magento\Customer\Model\Session $_customerSession
    ) {
        $this->_helper = $helper;
        $this->_customerSession = $_customerSession;
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

        $customerId = $context->getUserId();
        $this->_customerSession->setCustomerId($customerId);

        $baseCurrency = $this->_helper->getBaseCurrencyCode();
        $currentCurrency = $this->_helper->getCurrentCurrencyCode();
        $creditInfo = $this->_helper->customerCreditInfo($customerId);
        $remainingCredit = $creditInfo['remaining'];
        $remainingCreditFormatted = $this->_helper->getformattedPrice($remainingCredit);
        $remainingAmountCurrentCurrency = $this->_helper->convertCurrency($remainingCredit, $baseCurrency, $currentCurrency);
        $leftInCredit =  $this->_helper->getformattedPrice($this->_helper->getLeftInCredit());
        $currencySymbol = $this->_helper->getCurrencySymbol($currentCurrency);
        $getCurrentCode = $this->_helper->getCurrentCurrencyCode();
        $grandTotal = $this->_helper->getGrandTotal();


        $response['getcurrentcode'] = $getCurrentCode;
        $response['remainingcredit'] = $remainingCredit;
        $response['remainingcreditformatted'] = $remainingCreditFormatted;
        $response['remainingcreditcurrentcurrency'] = $remainingAmountCurrentCurrency;
        $response['leftincredit'] = $leftInCredit;
        $response['currencysymbol'] = $currencySymbol;
        $response['grand_total_formatted'] = $currencySymbol . $grandTotal;
        $response['grand_total'] = $grandTotal;

        return $response;
    }
}

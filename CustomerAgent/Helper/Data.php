<?php

namespace Orienteed\CustomerAgent\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogRule\Model\ResourceModel\Rule as CatalogRule;
use Magento\Framework\Stdlib\DateTime\Timezone;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CUSTOMER_AGENT_GENERAL_AGENT_ROLE = 'customer_agent/general/agent_role';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var CatalogRule
     */
    protected $_rule;

    /**
     * @var Timezone
     */
    protected $_timezone;

    /**
     * __construct
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerFactory $customerFactory
     * @param ProductRepository $productRepository
     * @param CatalogRule $_rule
     * @param Timezone $_timezone
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerFactory $customerFactory,
        ProductRepository $productRepository,
        CatalogRule $rule,
        Timezone $timezone
    ) {
        $this->customerFactory    = $customerFactory;
        $this->scopeConfig        = $scopeConfig;
        $this->_productRepository = $productRepository;
        $this->_rule              = $rule;
        $this->_timezone          = $timezone;
    }

    public function getAgentRole()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::CUSTOMER_AGENT_GENERAL_AGENT_ROLE, $storeScope);
    }

    /**
     * getProductBySku
     *
     * @param [string] $sku
     * @param [int] $storeId
     * @return object|array|null
     */
    public function getProductBySku($sku, $storeId)
    {
        return $this->_productRepository->get($sku, false, $storeId);
    }

    /**
     * getPriceAfterDiscount
     *
     * @param [object] $product
     * @return integer|null
    */
    public function getPriceAfterDiscount($product, $customerGroupId = null){
        if ($customerGroupId == null) {
            $customerGroupId = $product->getCustomerGroupId();
        }
        $currentTimestamp = strtotime($this->_timezone->formatDatetime(date("Y-m-d H:i:s"), \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, 'en_US'));
        $rulesData        = $this->_rule->getRulesFromProduct(
            $currentTimestamp,
            $product->getWebsiteIds(),
            $customerGroupId,
            $product->getId()
        );
        $priceAfterDiscount = 0;
        if (count($rulesData)) {
            $rulePercentage   = 0;
            $productPrice = $product->getPrice();
            foreach ($rulesData as $ruleData) {
                if (isset($ruleData['action_amount'])) {
                    $rulePercentage = $ruleData['action_amount'];
                }
            }
            $priceAfterDiscount = $productPrice - ($productPrice * ($rulePercentage / 100));
        }
        return $priceAfterDiscount;
    }
}

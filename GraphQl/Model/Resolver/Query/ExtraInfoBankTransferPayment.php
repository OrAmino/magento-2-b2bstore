<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Model\Resolver\Query;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class ExtraInfoBankTransferPayment implements ResolverInterface
{
    const PAYMENT_ACTIVE       = 'payment/banktransfer/active';
    const PAYMENT_INSTRUCTIONS = 'payment/banktransfer/instructions';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * __construct.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $instructions = '';

        if ($this->isEnable($storeId)) {
            $instructions = $this->scopeConfig->getValue(
                self::PAYMENT_INSTRUCTIONS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return [
            'instructions' => $instructions
        ];
    }

    private function isEnable($storeId)
    {
        return $this->scopeConfig->getValue(
            self::PAYMENT_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}

<?php

declare(strict_types=1);

namespace Orienteed\MoodleId\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;

/**
 * @inheritdoc
 */
class MoodleIdAttribute implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $customer = $value['model'];

        if ($customer->getCustomAttribute('moodle_id')) {
            $customerAttributeVal = $customer->getCustomAttribute('moodle_id')->getValue();
        } else {
            $customerAttributeVal = null;
        }

        return $customerAttributeVal;
    }
}

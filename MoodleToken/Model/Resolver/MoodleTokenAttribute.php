<?php

declare(strict_types=1);

namespace Orienteed\MoodleToken\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;

/**
 * @inheritdoc
 */
class MoodleTokenAttribute implements ResolverInterface
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

        if ($customer->getCustomAttribute('moodle_token')) {
            $customerAttributeVal = $customer->getCustomAttribute('moodle_token')->getValue();
        } else {
            $customerAttributeVal = null;
        }

        return $customerAttributeVal;
    }
}

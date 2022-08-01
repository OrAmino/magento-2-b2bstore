<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Plugin\Helper;

class Data
{
    public function afterGetPaymentisEnabled(\Webkul\CustomerCreditSystem\Helper\Data $subject, $result)
    {
        if ($subject->getCreditenabled()) {
            return true;
        } else {
            return false;
        }
    }
}

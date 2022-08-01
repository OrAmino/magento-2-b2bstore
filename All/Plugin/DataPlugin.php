<?php

namespace Orienteed\All\Plugin;

class DataPlugin
{
    public function beforeFormatPrice(
        \Mageplaza\RequestForQuote\Helper\Data $data,
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        # its create becaus $precision still gives null  
        #DEFAULT_PRECISION = 2;
        return [
            $amount,
            $includeContainer,
            $precision ?? 2,
            $scope,
            $currency
        ];
    }
}

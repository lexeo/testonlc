<?php
declare(strict_types=1);

namespace lexeo\testonlnc\Discount;

use lexeo\testonlnc\ProductCollection;

interface CalculatorStrategyInterface
{
    /**
     * @param ProductCollection $collection
     * @return float
     */
    public function calculateDiscount(ProductCollection $collection): float ;
}

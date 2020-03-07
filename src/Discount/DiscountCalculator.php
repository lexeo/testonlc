<?php
declare(strict_types=1);

namespace lexeo\testonlnc\Discount;

use lexeo\testonlnc\ProductCollection;


class DiscountCalculator
{
    /**
     * @var CalculatorStrategyInterface[]
     */
    private $queue;

    /**
     * @param CalculatorStrategyInterface ...$strategies
     */
    public function __construct(CalculatorStrategyInterface ...$strategies)
    {
        $this->queue = $strategies;
    }

    /**
     * @param ProductCollection $productCollection
     * @return float
     */
    public function calculateDiscount(ProductCollection $productCollection): float
    {
        $totalDiscount = 0.0;
        $tempProductCollection = clone $productCollection;
        foreach ($this->queue as $strategy) {
            $totalDiscount += $strategy->calculateDiscount($tempProductCollection);
        }
        return $totalDiscount;
    }
}

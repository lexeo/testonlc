<?php
declare(strict_types=1);

namespace lexeo\testonlnc\Discount\Strategy;

use lexeo\testonlnc\Discount\CalculatorStrategyInterface;
use lexeo\testonlnc\ProductCollection;

class ProductGroupStrategy implements CalculatorStrategyInterface
{
    /**
     * @var array [[group, percent], ...]
     */
    private $groupDiscountMap = [];

    /**
     * @see ProductGroupStrategy::addRule()
     * @param string[] $group
     * @param float $discountPercent
     */
    public function __construct(array $group, float $discountPercent)
    {
        $this->addRule($group, $discountPercent);
    }

    /**
     * Discount will be applied to the specified group of products
     * @param string[] $group group of products
     * @param float $discountPercent
     * @return $this
     */
    public function addRule(array $group, float $discountPercent): self
    {
        $this->groupDiscountMap[] = [$group, $discountPercent];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function calculateDiscount(ProductCollection $collection): float
    {
        if (!$collection->count()) {
            return 0.0;
        }

        $discount = 0.0;
        foreach ($this->groupDiscountMap as [$group, $percent]) {
            while (true) {
                $groupPrice = 0;
                foreach ($group as $item) {
                    if (!$collection->containsProductWithKey($item)) {
                        break 2;
                    }
                    $groupPrice += $collection->getProductByKey($item)->getPrice();
                }
                $discount += $groupPrice * $percent / 100;
                foreach ($group as $item) {
                    $collection->removeProductByKey($item, 1);
                }
            }
        }

        return $discount;
    }
}

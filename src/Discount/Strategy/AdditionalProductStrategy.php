<?php
declare(strict_types=1);

namespace lexeo\testonlnc\Discount\Strategy;

use lexeo\testonlnc\Discount\CalculatorStrategyInterface;
use lexeo\testonlnc\ProductCollection;


class AdditionalProductStrategy implements CalculatorStrategyInterface
{
    /**
     * @var array [[main, oneOf, discountPercent], ...],
     */
    private $rules = [];

    /**
     * @see AdditionalProductStrategy::addRule()
     * @param string $main
     * @param array $oneOf
     * @param float $discountPercent
     */
    public function __construct(string $main, array $oneOf, float $discountPercent)
    {
        $this->addRule($main, $oneOf, $discountPercent);
    }

    /**
     * Discount will be applied to one of the specified additional products
     * @param string $main main product
     * @param array $oneOf possible additional products
     * @param float $discountPercent
     * @return $this
     */
    public function addRule(string $main, array $oneOf, float $discountPercent): self
    {
        $this->rules[] = [$main, $oneOf, $discountPercent];
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
        foreach ($this->rules as [$main, $oneOf, $percent]) {
            while ($collection->containsProductWithKey($main)) {
                foreach ($oneOf as $key) {
                    if ($collection->containsProductWithKey($key)) {
                        $discount += $collection->getProductByKey($key)->getPrice() * $percent / 100;
                        $collection->removeProductByKey($main, 1);
                        $collection->removeProductByKey($key, 1);
                        continue 2;
                    }
                }
                break;
            }
        }

        return $discount;
    }
}

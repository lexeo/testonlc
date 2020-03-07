<?php
declare(strict_types=1);

namespace lexeo\testonlnc\Discount\Strategy;

use lexeo\testonlnc\Discount\CalculatorStrategyInterface;
use lexeo\testonlnc\ProductCollection;

class ProductNumberStrategy implements CalculatorStrategyInterface
{
    /**
     * @var array [num => percent, ...]
     */
    private $numPercentMap = [];
    /**
     * @var string[]
     */
    private $exclude = [];

    /**
     * @see ProductNumberStrategy::addRule()
     * @param int $productNum
     * @param float $discountPercent
     */
    public function __construct(int $productNum, float $discountPercent)
    {
        $this->addRule($productNum, $discountPercent);
    }

    /**
     * Discount will be applied to N products, where N >= the specified number
     * @param int $productNum Number of products
     * @param float $discountPercent
     * @return $this
     */
    public function addRule(int $productNum, float $discountPercent): self
    {
        $this->numPercentMap[$productNum] = $discountPercent;
        return $this;
    }

    /**
     * @param string[] $exclude
     * @return $this
     */
    public function excludeProducts(array $exclude): self
    {
        $this->exclude = $exclude;
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

        $totalPrice = 0;
        $count = 0;
        foreach ($collection as $product) {
            $amount = $collection->getProductAmount($product);
            if (!in_array($product->getKey(), $this->exclude, true)) {
                $totalPrice += $product->getPrice() * $amount;
                $count += $amount;
            }
        }

        krsort($this->numPercentMap);
        foreach ($this->numPercentMap as $num => $percent) {
            if ($count >= $num) {
                return $totalPrice * $percent / 100;
            }
        }
        return 0.0;
    }
}

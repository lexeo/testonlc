<?php
declare(strict_types=1);

use lexeo\testonlnc\Discount\DiscountCalculator;
use lexeo\testonlnc\Discount\Strategy\AdditionalProductStrategy;
use lexeo\testonlnc\Discount\Strategy\ProductNumberStrategy;
use lexeo\testonlnc\Discount\Strategy\ProductGroupStrategy;
use lexeo\testonlnc\ProductCollection;
use lexeo\testonlnc\Product;
use PHPUnit\Framework\TestCase;

class DiscountCalculatorTest extends TestCase
{
    /**
     * @var array [key => price, ...]
     */
    private static $productPriceMap = [
        'A' => 19.1,
        'B' => 10,
        'C' => 29.99,
        'D' => 3,
        'E' => 4.36,
        'F' => 13,
        'G' => 9.8,
        'H' => 4,
        'I' => 15,
        'J' => 9.99,
        'K' => 34.3,
        'L' => 31.1,
        'M' => 27.3,
    ];

    /**
     * @param string $key
     * @param int $amount [optional]
     * @return float
     */
    private static function priceFor(string $key, int $amount = 1): float
    {
        return self::$productPriceMap[$key] * $amount;
    }

    /**
     * @param array $productAmountMap [key => amount, ...]
     * @return ProductCollection
     */
    private function createAndFillProductCollection(array $productAmountMap): ProductCollection
    {
        $collection = new ProductCollection();
        foreach ($productAmountMap as $key => $amount) {
            $collection->addProduct(new Product($key, self::$productPriceMap[$key]), $amount);
        }
        return $collection;
    }



    public function testProductGroupStrategy(): void
    {
        $strategy = new ProductGroupStrategy(['A', 'B'], 10);
        $strategy->addRule(['D', 'E'], 6)
            ->addRule(['E', 'F', 'G'], 3);

        $this->assertEquals(0.0, $strategy->calculateDiscount(new ProductCollection()));

        $collection = $this->createAndFillProductCollection(['A' => 2, 'B' => 3]);
        $ma = 2;
        $expectedDiscount = (self::priceFor('A', $ma) + self::priceFor('B', $ma)) * 0.1;
        $this->assertEquals($expectedDiscount, $strategy->calculateDiscount($collection));

        $collection = $this->createAndFillProductCollection(['D' => 6, 'E' => 5]);
        $ma = 5;
        $expectedDiscount = (self::priceFor('D', $ma) + self::priceFor('E', $ma)) * 0.06;
        $this->assertEquals($expectedDiscount, $strategy->calculateDiscount($collection));

        $collection = $this->createAndFillProductCollection(['E' => 1, 'F' => 3, 'G' => 1]);
        $ma = 1;
        $expectedDiscount = (self::priceFor('E', $ma) + self::priceFor('F', $ma) + self::priceFor('G', $ma)) * 0.03;
        $this->assertEquals($expectedDiscount, $strategy->calculateDiscount($collection));
    }

    public function testAdditionalProductDiscount(): void
    {
        $strategy = new AdditionalProductStrategy('A', ['K', 'L', 'M'], 5);
        $this->assertEquals(0.0, $strategy->calculateDiscount(new ProductCollection()));

        $collection = $this->createAndFillProductCollection(['A' => 2, 'K' => 1]);
        $expectedDiscount = self::priceFor('K') * 0.05;
        $this->assertEquals($expectedDiscount, $strategy->calculateDiscount($collection));

        $collection = $this->createAndFillProductCollection(['A' => 2, 'M' => 2]);
        $expectedDiscount = self::priceFor('M', 2) * 0.05;
        $this->assertEquals($expectedDiscount, $strategy->calculateDiscount($collection));
    }


    public function testProductNumberDiscount(): void
    {
        $strategy = new ProductNumberStrategy(3, 5);
        $strategy->addRule(4, 10)
            ->addRule(5, 20);
        $strategy->excludeProducts(['A', 'C']);
        $this->assertEquals(0.0, $strategy->calculateDiscount(new ProductCollection()));

        $collection = $this->createAndFillProductCollection(['A' => 3, 'C' => 4, 'K' => 2]);
        $this->assertEquals(0.0, $strategy->calculateDiscount($collection));

        $collection = $this->createAndFillProductCollection(['B' => 3]);
        $expectedDiscount = self::priceFor('B', 3) * 0.05;
        $this->assertEquals($expectedDiscount, $strategy->calculateDiscount($collection));

        $collection = $this->createAndFillProductCollection(['B' => 7]);
        $expectedDiscount = self::priceFor('B', 7) *  0.2;
        $this->assertEquals($expectedDiscount, $strategy->calculateDiscount($collection));

        $collection = $this->createAndFillProductCollection(['A' => 4,'B' => 1, 'D' => 2]);
        $expectedDiscount = (self::priceFor('B') + self::priceFor('D', 2)) * 0.05;
        $this->assertEquals($expectedDiscount, $strategy->calculateDiscount($collection));
    }


    public function testMultipleStrategies(): void
    {
        $strategy1 = (new ProductGroupStrategy(['A', 'B'], 10))
            ->addRule(['D', 'E'], 6)
            ->addRule(['E', 'F', 'G'], 3);

        $strategy2 = (new AdditionalProductStrategy('A', ['K', 'L', 'M'], 5));

        $strategy3 = (new ProductNumberStrategy(3, 5))
            ->addRule(4, 10)
            ->addRule(5, 20)
            ->excludeProducts(['A', 'C']);

        $strategy = new DiscountCalculator($strategy1, $strategy2, $strategy3);
        $this->assertEquals(0.0, $strategy->calculateDiscount(new ProductCollection()));

        $collection = $this->createAndFillProductCollection([
            'A' => 2,
            'B' => 1,
            'C' => 5,
            'D' => 2,
            'G' => 1,
            'M' => 2,
        ]);

        $res1 = (self::priceFor('A') + self::priceFor('B')) * 0.1;
        $res2 = self::priceFor('M') * 0.05;
        $res3 = (self::priceFor('D', 2) + self::priceFor('G') + self::priceFor('M')) * 0.1;
        $expectedDiscount = $res1 + $res2 + $res3;

        $this->assertEquals($expectedDiscount, $strategy->calculateDiscount($collection));
    }
}

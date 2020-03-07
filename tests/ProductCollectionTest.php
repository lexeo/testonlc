<?php
declare(strict_types=1);

use lexeo\testonlnc\Product;
use lexeo\testonlnc\ProductCollection;
use PHPUnit\Framework\TestCase;

class ProductCollectionTest extends TestCase
{

    public function testAddsProducts(): void
    {
        $collection = new ProductCollection();

        $collection->addProduct(new Product('A', 1), 1);
        $this->assertTrue($collection->containsProductWithKey('A'));
        $this->assertTrue(isset($collection['A']));

        $collection->addProduct(new Product('C', 1), 1);
        $this->assertTrue($collection->containsProductWithKey('C'));
        $this->assertTrue(isset($collection['C']));
    }


    public function testProvidesAccessToProducts(): void
    {
        $collection = new ProductCollection();

        $product = new Product(uniqid('p_', true), 1);
        $collection->addProduct($product, 3);

        $this->assertTrue($collection->containsProductWithKey($product->getKey()));
        $this->assertSame($collection->getProductByKey($product->getKey()), $product);
        $this->assertSame($collection[$product->getKey()], $product);

        $this->assertEquals(3, $collection->getProductAmount($product));
    }


    public function testIterable(): void
    {
        $collection = new ProductCollection();

        $products = [
            new Product('A', 1),
            new Product('A', 1),
            new Product('B', 1),
            new Product('C', 1),
            new Product('D', 1)
        ];
        foreach ($products as $product) {
            $collection->addProduct($product);
        }

        $iteration = 0;
        foreach ($collection as $product) {
            $iteration++;
            $this->assertContainsEquals($product, $products);
        }
        $this->assertEquals(4, $iteration);
    }


    public function testRemovesProducts(): void
    {
        $collection = new ProductCollection();

        $collection->addProduct(new Product('A', 1), 1);
        $collection->removeProductByKey('A');
        $this->assertFalse($collection->containsProductWithKey('A'));


        $collection->addProduct(new Product('B', 1), 3);
        $collection->removeProductByKey('B', 1);
        $this->assertTrue($collection->containsProductWithKey('B'));
        unset($collection['B']);
        $this->assertFalse($collection->containsProductWithKey('B'));


        $productC = new Product('C', 1);
        $collection->addProduct($productC, 5);

        $collection->removeProduct($productC, 2);
        $this->assertTrue($collection->containsProductWithKey($productC->getKey()));
        $this->assertEquals(3, $collection->getProductAmount($productC));

        $collection->removeProduct($productC);
        $this->assertFalse($collection->containsProductWithKey($productC->getKey()));
    }


    public function testCountsProductsCorrectly(): void
    {
        $collection = new ProductCollection();
        $this->assertEquals(0, $collection->count());

        $collection->addProduct(new Product('A', 1), 3);
        $this->assertEquals(3, $collection->count());
        $this->assertEquals(1, $collection->countDistinct());

        $productB = new Product('B', 1);
        $collection->addProduct($productB, 4);
        $this->assertEquals(7, $collection->count());
        $this->assertEquals(2, $collection->countDistinct());

        $collection->removeProduct($productB, 2);
        $this->assertEquals(5, $collection->count());
        $this->assertEquals(2, $collection->countDistinct());

        unset($collection[$productB->getKey()]);
        $this->assertEquals(3, $collection->count());
        $this->assertEquals(1, $collection->countDistinct());
    }
}

<?php
declare(strict_types=1);

namespace lexeo\testonlnc;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;

class ProductCollection implements Countable, ArrayAccess, IteratorAggregate
{
    /**
     * @var array|array[] [index => [Product, amount], ...]
     */
    private $products = [];
    private $productsCnt = 0;

    /**
     * @param Product $product
     * @param int $amount
     * @return $this
     */
    public function addProduct(Product $product, int $amount = 1): self
    {
        $key = $product->getKey();
        if (isset($this->products[$key])) {
            $this->products[$key][1] += $amount;
        } else {
            $this->products[$key] = [$product, $amount];
        }
        $this->productsCnt += $amount;

        return $this;
    }

    /**
     * @param string $key
     * @return Product
     */
    public function getProductByKey(string $key): Product
    {
        return $this->products[$key][0];
    }

    /**
     * @param Product $product
     * @return int
     */
    public function getProductAmount(Product $product): int
    {
        return $this->products[$product->getKey()][1] ?? 0;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function containsProductWithKey(string $key): bool
    {
        return isset($this->products[$key]);
    }

    /**
     * @param Product $product
     * @param int $amount [optional, default=-1]
     * @return $this
     */
    public function removeProduct(Product $product, int $amount = -1): self
    {
        return $this->removeProductByKey($product->getKey(), $amount);
    }

    /**
     * @param string $key
     * @param int $amount [optional, default=-1]
     * @return $this
     */
    public function removeProductByKey(string $key, int $amount = -1): self
    {
        if (-1 === $amount) {
            $this->offsetUnset($key);
        } elseif (isset($this->products[$key])) {
            $current = &$this->products[$key][1];
            $this->productsCnt -= min($current, $amount);
            $current -= $amount;
            if ($current <= 0) {
                unset($this->products[$key]);
            }
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function count() : int
    {
        return $this->productsCnt;
    }

    /**
     * @return int
     */
    public function countDistinct() : int
    {
        return count($this->products);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->products);
    }

    /**
     * @param string $offset
     * @return Product
     */
    public function offsetGet($offset): Product
    {
        return $this->products[$offset][0];
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Array-style assignment is not allowed for a collection. Please use built-in methods.');
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        if (array_key_exists($offset, $this->products)) {
            $this->productsCnt -= $this->products[$offset][1];
        }
        unset($this->products[$offset]);
    }

    /**
     * @return Product[]|iterable
     */
    public function getIterator(): iterable
    {
        return new ArrayIterator(array_column($this->products, 0));
    }
}

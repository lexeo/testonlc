<?php
declare(strict_types=1);

namespace lexeo\testonlnc;

class Product
{
    /**
     * @var string
     */
    private $title;
    /**
     * @var float
     */
    private $price;

    /**
     * @param string $title
     * @param float $price
     */
    public function __construct(string $title, float $price)
    {
        $this->title = $title;
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->title;
    }
}

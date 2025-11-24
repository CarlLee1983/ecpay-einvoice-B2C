<?php

declare(strict_types=1);

namespace ecPay\eInvoice\DTO;

use InvalidArgumentException;

/**
 * 一般折讓作業的商品 DTO（以整數計價）。
 */
final class AllowanceItemDto implements ItemDtoInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var int
     */
    private int $quantity;

    /**
     * @var string
     */
    private string $unit;

    /**
     * @var int
     */
    private int $price;

    /**
     * @param string $name
     * @param int $quantity
     * @param string $unit
     * @param int $price
     */
    public function __construct(string $name, int $quantity, string $unit, int $price)
    {
        $name = trim($name);
        $unit = trim($unit);

        if ($name === '') {
            throw new InvalidArgumentException('Allowance item name cannot be empty.');
        }

        if ($unit === '') {
            throw new InvalidArgumentException('Allowance item unit cannot be empty.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Allowance item quantity must be greater than 0.');
        }

        if ($price <= 0) {
            throw new InvalidArgumentException('Allowance item price must be greater than 0.');
        }

        $this->name = $name;
        $this->quantity = $quantity;
        $this->unit = $unit;
        $this->price = $price;
    }

    /**
     * @param array $item
     * @return self
     */
    public static function fromArray(array $item): self
    {
        foreach (['name', 'quantity', 'unit', 'price'] as $field) {
            if (!array_key_exists($field, $item)) {
                throw new InvalidArgumentException('Items field' . $field . ' not exists.');
            }
        }

        return new self(
            (string) $item['name'],
            (int) $item['quantity'],
            (string) $item['unit'],
            (int) $item['price']
        );
    }

    /**
     * @return array
     */
    public function toPayload(): array
    {
        return [
            'ItemName' => $this->name,
            'ItemCount' => $this->quantity,
            'ItemWord' => $this->unit,
            'ItemPrice' => $this->price,
            'ItemAmount' => $this->getAmount(),
        ];
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return (float) ($this->quantity * $this->price);
    }
}

<?php

declare(strict_types=1);

namespace ecPay\eInvoice\DTO;

use InvalidArgumentException;

/**
 * 一般開立發票的商品 DTO。
 */
final class InvoiceItemDto implements ItemDtoInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var float
     */
    private float $quantity;

    /**
     * @var string
     */
    private string $unit;

    /**
     * @var float
     */
    private float $price;

    /**
     * @var string|null
     */
    private ?string $taxType;

    /**
     * @param string $name
     * @param float $quantity
     * @param string $unit
     * @param float $price
     * @param string|null $taxType
     */
    public function __construct(string $name, float $quantity, string $unit, float $price, ?string $taxType = null)
    {
        $name = trim($name);
        $unit = trim($unit);

        if ($name === '') {
            throw new InvalidArgumentException('Item name cannot be empty.');
        }

        if ($unit === '') {
            throw new InvalidArgumentException('Item unit cannot be empty.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Item quantity must be greater than 0.');
        }

        if ($price <= 0) {
            throw new InvalidArgumentException('Item price must be greater than 0.');
        }

        $this->name = $name;
        $this->quantity = $quantity;
        $this->unit = $unit;
        $this->price = $price;
        $this->taxType = $taxType !== null ? trim($taxType) : null;
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

        $taxType = $item['taxType'] ?? null;

        return new self(
            (string) $item['name'],
            (float) $item['quantity'],
            (string) $item['unit'],
            (float) $item['price'],
            $taxType !== null ? (string) $taxType : null
        );
    }

    /**
     * @return array
     */
    public function toPayload(): array
    {
        $payload = [
            'ItemName' => $this->name,
            'ItemCount' => $this->quantity,
            'ItemWord' => $this->unit,
            'ItemPrice' => $this->price,
            'ItemAmount' => $this->getAmount(),
        ];

        if ($this->taxType !== null && $this->taxType !== '') {
            $payload['ItemTaxType'] = $this->taxType;
        }

        return $payload;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->quantity * $this->price;
    }
}

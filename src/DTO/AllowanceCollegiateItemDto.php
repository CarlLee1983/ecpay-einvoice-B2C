<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\DTO;

use InvalidArgumentException;

/**
 * 跨機關折讓作業的商品 DTO，需要指定稅別並允許小數。
 */
final readonly class AllowanceCollegiateItemDto implements ItemDtoInterface
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
     * @var string
     */
    private string $taxType;

    /**
     * @param string $name
     * @param float $quantity
     * @param string $unit
     * @param float $price
     * @param string $taxType
     */
    public function __construct(string $name, float $quantity, string $unit, float $price, string $taxType)
    {
        $trimmedName = trim($name);
        $trimmedUnit = trim($unit);
        $trimmedTaxType = trim($taxType);

        if ($trimmedName === '') {
            throw new InvalidArgumentException('Allowance collegiate item name cannot be empty.');
        }

        if ($trimmedUnit === '') {
            throw new InvalidArgumentException('Allowance collegiate item unit cannot be empty.');
        }

        if ($trimmedTaxType === '') {
            throw new InvalidArgumentException('Allowance collegiate item tax type cannot be empty.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Allowance collegiate item quantity must be greater than 0.');
        }

        if ($price <= 0) {
            throw new InvalidArgumentException('Allowance collegiate item price must be greater than 0.');
        }

        $this->name = $trimmedName;
        $this->quantity = $quantity;
        $this->unit = $trimmedUnit;
        $this->price = $price;
        $this->taxType = $trimmedTaxType;
    }

    /**
     * @param array $item
     * @return self
     */
    #[\Override]
    public static function fromArray(array $item): self
    {
        foreach (['name', 'quantity', 'unit', 'price', 'taxType'] as $field) {
            if (!array_key_exists($field, $item)) {
                throw new InvalidArgumentException('折讓項目缺少欄位: ' . $field);
            }
        }

        return new self(
            (string) $item['name'],
            (float) $item['quantity'],
            (string) $item['unit'],
            (float) $item['price'],
            (string) $item['taxType']
        );
    }

    /**
     * @return array
     */
    #[\Override]
    public function toPayload(): array
    {
        return [
            'ItemName' => $this->name,
            'ItemCount' => $this->quantity,
            'ItemWord' => $this->unit,
            'ItemPrice' => $this->price,
            'ItemAmount' => $this->getAmount(),
            'ItemTaxType' => $this->taxType,
        ];
    }

    /**
     * @return float
     */
    #[\Override]
    public function getAmount(): float
    {
        return $this->quantity * $this->price;
    }
}

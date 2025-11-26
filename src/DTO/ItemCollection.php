<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\DTO;

use CarlLee\EcPay\Core\DTO\ItemCollection as CoreItemCollection;
use CarlLee\EcPay\Core\DTO\ItemDtoInterface;
use InvalidArgumentException;

/**
 * 項目集合類別。
 *
 * 繼承自 Core 的 ItemCollection，保持向下相容。
 */
class ItemCollection extends CoreItemCollection
{
    /**
     * 由混合輸入（陣列或 DTO）建立集合。
     *
     * @param array<int, ItemDtoInterface|array<string, mixed>> $items
     * @param callable $arrayConverter
     * @return self
     */
    public static function fromMixed(array $items, callable $arrayConverter): self
    {
        $collection = new self();

        foreach ($items as $item) {
            if (is_array($item)) {
                $item = $arrayConverter($item);
            }

            if (!$item instanceof ItemDtoInterface) {
                throw new InvalidArgumentException('Each item must implement ItemDtoInterface.');
            }

            $collection->add($item);
        }

        return $collection;
    }

    /**
     * 轉換為 payload 陣列，可對每個項目進行額外轉換。
     *
     * @param callable(array<string, mixed>, ItemDtoInterface): array<string, mixed> $transform
     * @return array<int, array<string, mixed>>
     */
    public function mapPayload(callable $transform): array
    {
        $payload = [];

        foreach ($this->all() as $item) {
            $payload[] = $transform($item->toArray(), $item);
        }

        return $payload;
    }

    /**
     * 計算所有項目的金額總和。
     *
     * @return float
     */
    public function sumAmount(): float
    {
        $total = 0.0;

        foreach ($this->all() as $item) {
            $amount = $item->toArray()['ItemAmount'] ?? 0;
            $total += (float) $amount;
        }

        return $total;
    }
}

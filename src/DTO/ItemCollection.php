<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\DTO;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * 商品集合的 Value Object。
 *
 * @implements IteratorAggregate<int, ItemDtoInterface>
 */
final class ItemCollection implements IteratorAggregate, \Countable
{
    /**
     * @var array<int,ItemDtoInterface>
     */
    private array $items = [];

    /**
     * @param array<int,ItemDtoInterface> $items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * add
     *
     * @param ItemDtoInterface $item
     * @return self
     */
    public function add(ItemDtoInterface $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * 由混合輸入（陣列或 DTO）建立集合。
     *
     * @param array<int,ItemDtoInterface|array<string,mixed>> $items
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
     * @return Traversable<int,ItemDtoInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * count
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * isEmpty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /**
     * sumAmount
     *
     * @return float
     */
    public function sumAmount(): float
    {
        $total = 0.0;

        foreach ($this->items as $item) {
            $total += $item->getAmount();
        }

        return $total;
    }

    /**
     * toArray
     *
     * @return array<int,array<string,mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (ItemDtoInterface $item): array => $item->toPayload(),
            $this->items
        );
    }

    /**
     * @param callable $transform
     * @return array<int,array<string,mixed>>
     */
    public function mapPayload(callable $transform): array
    {
        $payload = [];

        foreach ($this->items as $item) {
            $payload[] = $transform($item->toPayload(), $item);
        }

        return $payload;
    }
}

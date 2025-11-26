<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Tests\Unit;

use CarlLee\EcPayB2C\DTO\InvoiceItemDto;
use PHPUnit\Framework\TestCase;

/**
 * 單元測試基底類別。
 *
 * 提供單元測試所需的共用設定。
 * 這些測試不需要網路連線，使用 Mock 進行測試。
 *
 * @group unit
 */
abstract class UnitTestCase extends TestCase
{
    protected string $merchantId = '2000132';
    protected string $hashKey = 'ejCk326UnaZWKisg';
    protected string $hashIV = 'q9jcZX8Ib9LM8wYk';

    /**
     * 建立預設的發票商品項目。
     *
     * @param array<int,array<string,mixed>> $items
     * @return InvoiceItemDto[]
     */
    protected function makeItems(array $items = []): array
    {
        if ($items === []) {
            $items = [
                [
                    'name' => '商品範例',
                    'quantity' => 1,
                    'unit' => '個',
                    'price' => 100,
                ],
            ];
        }

        return array_map(
            static fn (array $item): InvoiceItemDto => InvoiceItemDto::fromArray($item),
            $items
        );
    }
}

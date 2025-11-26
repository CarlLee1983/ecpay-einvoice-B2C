<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Tests\Integration;

use CarlLee\EcPayB2C\DTO\InvoiceItemDto;
use CarlLee\EcPayB2C\EcPayClient;
use PHPUnit\Framework\TestCase;

/**
 * 整合測試基底類別。
 *
 * 提供與 ECPay API 實際通訊所需的共用設定。
 * 這些測試需要網路連線且會呼叫 ECPay 測試伺服器。
 *
 * @group integration
 */
abstract class IntegrationTestCase extends TestCase
{
    protected EcPayClient $client;

    protected string $merchantId;
    protected string $hashKey;
    protected string $hashIV;
    protected string $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = $_ENV['SERVER'] ?? 'https://einvoice-stage.ecpay.com.tw';
        $this->merchantId = $_ENV['MERCHANT_ID'] ?? '2000132';
        $this->hashKey = $_ENV['HASH_KEY'] ?? 'ejCk326UnaZWKisg';
        $this->hashIV = $_ENV['HASH_IV'] ?? 'q9jcZX8Ib9LM8wYk';

        $this->client = new EcPayClient(
            $this->server,
            $this->hashKey,
            $this->hashIV
        );
    }

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

    /**
     * 產生唯一的 RelateNumber。
     */
    protected function generateRelateNumber(string $prefix = 'YEP'): string
    {
        return $prefix . date('YmdHis') . rand(10, 99);
    }
}

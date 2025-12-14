<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

use CarlLee\EcPayB2C\Contracts\CommandInterface;

/**
 * 電子發票介面。
 *
 * 定義可送往綠界 API 的內容物件必須提供的額外能力。
 *
 * `getPayload()`、`getRequestPath()`、金鑰注入與編碼器等基本契約，來自 `CommandInterface`。
 */
interface InvoiceInterface extends CommandInterface
{
    /**
     * 取得可傳輸的加密內容（通常包含加密後的 `Data`）。
     *
     * @return array<string, mixed>
     */
    public function getContent(): array;
}

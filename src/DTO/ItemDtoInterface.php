<?php

declare(strict_types=1);

namespace ecPay\eInvoice\DTO;

/**
 * 商品項目的最小介面。
 */
interface ItemDtoInterface
{
    /**
     * 由陣列建立物件，供舊介面轉換之用。
     *
     * @param array $item
     */
    public static function fromArray(array $item): self;

    /**
     * 轉為 API 需要的欄位結構。
     *
     * @return array<string,mixed>
     */
    public function toPayload(): array;

    /**
     * 取得金額（單價 * 數量）。
     */
    public function getAmount(): float;
}

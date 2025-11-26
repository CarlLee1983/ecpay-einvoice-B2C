<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

/**
 * 電子發票介面。
 *
 * 定義電子發票物件必須實作的方法。
 * 注意：validation() 已移至 AbstractContent 作為 protected 方法，
 * 由 getPayload() 內部呼叫，不再暴露於介面。
 */
interface InvoiceInterface
{
    /**
     * 取得加密後的發票內容。
     *
     * @return array<string, mixed>
     */
    public function getContent(): array;

    /**
     * 取得未加密的領域層資料。
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array;
}

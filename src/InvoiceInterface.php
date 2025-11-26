<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

interface InvoiceInterface
{
    /**
     * Get the invoice content.
     *
     * @return array
     */
    public function getContent(): array;

    /**
     * 取得未加密的領域層資料。
     *
     * @return array
     */
    public function getPayload(): array;

    /**
     * Validation content.
     *
     * @return void
     */
    public function validation();
}

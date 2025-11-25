<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Contracts;

use CarlLee\EcPayB2C\Infrastructure\PayloadEncoder;

/**
 * 封裝對 EcPay API 的命令介面。
 */
interface CommandInterface
{
    /**
     * 取得 API 路徑。
     */
    public function getRequestPath(): string;

    /**
     * 取得未加密的請求 payload。
     *
     * @return array<string,mixed>
     */
    public function getPayload(): array;

    /**
     * 取得可用於加解密的 PayloadEncoder。
     */
    public function getPayloadEncoder(): PayloadEncoder;

    /**
     * 調整 HashKey，讓命令採用客戶端提供的金鑰。
     *
     * @return static
     */
    public function setHashKey(string $key);

    /**
     * 調整 HashIV，讓命令採用客戶端提供的金鑰。
     *
     * @return static
     */
    public function setHashIV(string $iv);
}

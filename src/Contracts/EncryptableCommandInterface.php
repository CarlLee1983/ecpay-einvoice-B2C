<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Contracts;

/**
 * 可產生加密傳輸內容的命令介面。
 *
 * 用於描述「可送往綠界 API」且同時能產生已加密 `Data` 的 payload 物件。
 *
 * @phpstan-type ApiPayload array{MerchantID: string, RqHeader: array<string, mixed>, Data: array<string, mixed>}
 * @phpstan-type EncodedPayload array{MerchantID: string, RqHeader: array<string, mixed>, Data: string}
 */
interface EncryptableCommandInterface extends CommandInterface
{
    /**
     * 取得可傳輸的加密內容（`Data` 已加密）。
     *
     * @return array<string, mixed>
     * @phpstan-return EncodedPayload
     */
    public function getContent(): array;
}


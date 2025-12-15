<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Contracts;

use CarlLee\EcPay\Core\Contracts\PayloadEncoderInterface;

/**
 * 可由 Client 發送，且能解碼回應的命令介面。
 *
 * 目標是讓 `EcPayClient` 不必假設回應格式與解碼方式，改由命令物件定義：
 * - 如何產生可傳輸內容（`getContent()`/`getTransportBody()`）
 * - 如何將 API 回應解碼成 `Response::setData()` 需要的結構
 */
interface SendableCommandInterface extends EncryptableCommandInterface
{
    /**
     * 解碼 API 回應，並回傳要寫入 `Response::setData()` 的陣列。
     *
     * @param array<string, mixed> $responseBody
     * @return array<string, mixed>
     */
    public function decodeResponse(array $responseBody, PayloadEncoderInterface $payloadEncoder): array;
}


<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

/**
 * Payload 例外。
 *
 * 當 payload 結構或資料無效時拋出此例外。
 */
class PayloadException extends EcPayException
{
    /**
     * Payload 結構無效。
     *
     * @param string $reason 原因說明
     * @return static
     */
    public static function invalidStructure(string $reason = ''): static
    {
        $message = $reason !== ''
            ? "Payload 結構無效：{$reason}"
            : 'Payload 結構無效。';

        return new static($message);
    }

    /**
     * Payload 資料格式無效。
     *
     * @param string $reason 原因說明
     * @return static
     */
    public static function invalidData(string $reason = ''): static
    {
        $message = $reason !== ''
            ? "Payload 資料格式無效：{$reason}"
            : 'Payload 資料格式無效。';

        return new static($message);
    }

    /**
     * 缺少必要的 Data 區塊。
     *
     * @return static
     */
    public static function missingData(): static
    {
        return static::invalidStructure('缺少 Data 區塊');
    }
}

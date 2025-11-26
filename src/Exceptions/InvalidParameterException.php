<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

/**
 * 參數格式或值無效時拋出的例外。
 *
 * 用於方法參數驗證錯誤，例如：
 * - 參數格式錯誤（如日期格式、統編長度）
 * - 參數值超出允許範圍
 * - 參數類型不正確
 *
 * @deprecated 請改用 ValidationException 或 PayloadException，
 *             本類別將在未來版本移除。
 */
class InvalidParameterException extends EcPayException
{
    /**
     * 參數為空。
     *
     * @param string $paramName 參數名稱
     * @return static
     */
    public static function empty(string $paramName): static
    {
        return new static("{$paramName} 不可為空。");
    }

    /**
     * 參數格式無效。
     *
     * @param string $paramName 參數名稱
     * @param string $reason 原因說明
     * @return static
     */
    public static function invalid(string $paramName, string $reason = ''): static
    {
        $message = $reason !== ''
            ? "{$paramName} 格式無效：{$reason}"
            : "{$paramName} 格式無效。";

        return new static($message);
    }

    /**
     * 參數長度超出限制。
     *
     * @param string $paramName 參數名稱
     * @param int $maxLength 最大長度
     * @return static
     */
    public static function tooLong(string $paramName, int $maxLength): static
    {
        return new static("{$paramName} 不可超過 {$maxLength} 個字元。");
    }

    /**
     * 參數值不在允許範圍內。
     *
     * @param string $paramName 參數名稱
     * @param array<int|string> $allowedValues 允許的值
     * @return static
     */
    public static function notInRange(string $paramName, array $allowedValues): static
    {
        $values = implode(', ', $allowedValues);

        return new static("{$paramName} 必須為下列值之一：{$values}");
    }
}

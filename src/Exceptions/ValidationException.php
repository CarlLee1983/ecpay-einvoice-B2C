<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

/**
 * 驗證例外。
 *
 * 當輸入參數驗證失敗時拋出此例外。
 */
class ValidationException extends EcPayException
{
    /**
     * 驗證失敗的欄位名稱。
     *
     * @var string|null
     */
    protected ?string $field = null;

    /**
     * 建立驗證例外。
     *
     * @param string $message 錯誤訊息
     * @param string|null $field 失敗的欄位名稱
     * @param array<string, mixed> $context 額外上下文資訊
     */
    public static function make(
        string $message,
        ?string $field = null,
        array $context = []
    ): static {
        $exception = new static($message, 0, null, $context);
        $exception->field = $field;

        if ($field !== null) {
            $exception->addContext('field', $field);
        }

        return $exception;
    }

    /**
     * 欄位為必填但為空。
     *
     * @param string $field 欄位名稱
     * @return static
     */
    public static function required(string $field): static
    {
        return static::make("{$field} 為必填欄位。", $field);
    }

    /**
     * 欄位值無效。
     *
     * @param string $field 欄位名稱
     * @param string $reason 原因說明
     * @return static
     */
    public static function invalid(string $field, string $reason = ''): static
    {
        $message = $reason !== ''
            ? "{$field} 格式無效：{$reason}"
            : "{$field} 格式無效。";

        return static::make($message, $field);
    }

    /**
     * 欄位值超出長度限制。
     *
     * @param string $field 欄位名稱
     * @param int $maxLength 最大長度
     * @return static
     */
    public static function tooLong(string $field, int $maxLength): static
    {
        return static::make(
            "{$field} 不可超過 {$maxLength} 個字元。",
            $field,
            ['max_length' => $maxLength]
        );
    }

    /**
     * 欄位值不在允許範圍內。
     *
     * @param string $field 欄位名稱
     * @param array<int|string> $allowedValues 允許的值
     * @return static
     */
    public static function notInRange(string $field, array $allowedValues): static
    {
        $values = implode(', ', $allowedValues);

        return static::make(
            "{$field} 必須為下列值之一：{$values}",
            $field,
            ['allowed_values' => $allowedValues]
        );
    }

    /**
     * 取得驗證失敗的欄位名稱。
     *
     * @return string|null
     */
    public function getField(): ?string
    {
        return $this->field;
    }
}

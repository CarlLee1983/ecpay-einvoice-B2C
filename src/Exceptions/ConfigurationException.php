<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

/**
 * 設定例外。
 *
 * 當套件設定相關錯誤時拋出此例外。
 */
class ConfigurationException extends EcPayException
{
    /**
     * 設定值為空或缺少。
     *
     * @param string $configKey 設定鍵名
     * @return static
     */
    public static function missing(string $configKey): static
    {
        return new static("{$configKey} 設定為空或缺少。");
    }

    /**
     * 設定值無效。
     *
     * @param string $configKey 設定鍵名
     * @param string $reason 原因說明
     * @return static
     */
    public static function invalid(string $configKey, string $reason = ''): static
    {
        $message = $reason !== ''
            ? "{$configKey} 設定無效：{$reason}"
            : "{$configKey} 設定無效。";

        return new static($message);
    }
}

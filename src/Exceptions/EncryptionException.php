<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

/**
 * 加密例外。
 *
 * 當 AES 加解密操作失敗時拋出此例外。
 */
class EncryptionException extends EcPayException
{
    /**
     * 加密失敗。
     *
     * @param string $reason 原因說明
     * @return static
     */
    public static function encryptionFailed(string $reason = ''): static
    {
        $message = $reason !== ''
            ? "加密失敗：{$reason}"
            : '加密失敗。';

        return new static($message);
    }

    /**
     * 解密失敗。
     *
     * @param string $reason 原因說明
     * @return static
     */
    public static function decryptionFailed(string $reason = ''): static
    {
        $message = $reason !== ''
            ? "解密失敗：{$reason}"
            : '解密失敗。';

        return new static($message);
    }

    /**
     * 金鑰無效。
     *
     * @param string $keyType 金鑰類型（HashKey 或 HashIV）
     * @return static
     */
    public static function invalidKey(string $keyType): static
    {
        return new static("{$keyType} 為空或無效。");
    }
}

<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

/**
 * 加解密相關錯誤時拋出的例外。
 *
 * 用於 AES 加解密過程中的錯誤，例如：
 * - HashKey 或 HashIV 為空或無效
 * - 加密失敗
 * - 解密失敗或資料損壞
 */
class EncryptionException extends EcPayException
{
}


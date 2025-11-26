<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

use CarlLee\EcPay\Core\Exceptions\EncryptionException as CoreEncryptionException;

/**
 * 加解密錯誤例外。
 *
 * 繼承自 Core 的 EncryptionException，保持向下相容。
 */
class EncryptionException extends CoreEncryptionException
{
}

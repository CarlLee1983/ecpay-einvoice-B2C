<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Infrastructure;

use CarlLee\EcPay\Core\Infrastructure\CipherService as CoreCipherService;

/**
 * AES 加解密服務。
 *
 * 繼承自 Core 的 CipherService，保持向下相容。
 */
class CipherService extends CoreCipherService
{
}

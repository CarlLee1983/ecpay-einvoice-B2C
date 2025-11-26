<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Infrastructure;

use CarlLee\EcPay\Core\Infrastructure\PayloadEncoder as CorePayloadEncoder;

/**
 * Payload 編碼器。
 *
 * 繼承自 Core 的 PayloadEncoder，保持向下相容。
 */
class PayloadEncoder extends CorePayloadEncoder
{
}

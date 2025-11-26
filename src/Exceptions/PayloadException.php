<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

use CarlLee\EcPay\Core\Exceptions\PayloadException as CorePayloadException;

/**
 * Payload 例外。
 *
 * 繼承自 Core 的 PayloadException，保持向下相容。
 */
class PayloadException extends CorePayloadException
{
}

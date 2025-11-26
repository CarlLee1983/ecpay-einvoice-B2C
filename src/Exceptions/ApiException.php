<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

use CarlLee\EcPay\Core\Exceptions\ApiException as CoreApiException;

/**
 * API 例外。
 *
 * 繼承自 Core 的 ApiException，保持向下相容。
 */
class ApiException extends CoreApiException
{
}

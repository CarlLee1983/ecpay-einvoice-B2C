<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

use CarlLee\EcPay\Core\Exceptions\ValidationException as CoreValidationException;

/**
 * 驗證例外。
 *
 * 繼承自 Core 的 ValidationException，保持向下相容。
 * 所有驗證相關的靜態方法（required, invalid, tooLong, notInRange 等）
 * 都已在 Core 中實作。
 */
class ValidationException extends CoreValidationException
{
}

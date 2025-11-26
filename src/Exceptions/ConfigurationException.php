<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

use CarlLee\EcPay\Core\Exceptions\ConfigurationException as CoreConfigurationException;

/**
 * 設定錯誤例外。
 *
 * 繼承自 Core 的 ConfigurationException，保持向下相容。
 */
class ConfigurationException extends CoreConfigurationException
{
}

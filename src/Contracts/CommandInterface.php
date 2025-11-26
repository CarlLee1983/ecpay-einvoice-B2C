<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Contracts;

use CarlLee\EcPay\Core\Contracts\CommandInterface as CoreCommandInterface;

/**
 * 命令介面。
 *
 * 繼承自 Core 的 CommandInterface，保持向下相容。
 */
interface CommandInterface extends CoreCommandInterface
{
}

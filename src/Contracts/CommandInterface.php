<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Contracts;

use CarlLee\EcPay\Core\Contracts\CommandInterface as CoreCommandInterface;

/**
 * 命令介面。
 *
 * 繼承自 Core 的 CommandInterface，保持向下相容。
 *
 * 若命令物件需要提供「加密後可傳輸內容」（`Data` 已加密），建議改 type-hint `EncryptableCommandInterface`。
 * 若命令物件會交由 `EcPayClient` 發送，建議改 type-hint `SendableCommandInterface`。
 */
interface CommandInterface extends CoreCommandInterface
{
}

<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\DTO;

use CarlLee\EcPay\Core\DTO\ItemDtoInterface as CoreItemDtoInterface;

/**
 * 項目 DTO 介面。
 *
 * 繼承自 Core 的 ItemDtoInterface，保持向下相容。
 */
interface ItemDtoInterface extends CoreItemDtoInterface
{
}

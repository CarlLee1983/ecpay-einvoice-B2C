<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

use CarlLee\EcPayB2C\Contracts\EncryptableCommandInterface;

/**
 * 電子發票介面。
 *
 * 定義可送往綠界 API 的內容物件必須提供的額外能力。
 *
 * @deprecated since 4.1.1 請改用 `CarlLee\EcPayB2C\Contracts\EncryptableCommandInterface`
 */
interface InvoiceInterface extends EncryptableCommandInterface
{
}

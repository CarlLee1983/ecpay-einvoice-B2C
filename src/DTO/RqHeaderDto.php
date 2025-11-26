<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\DTO;

use CarlLee\EcPay\Core\DTO\RqHeaderDto as CoreRqHeaderDto;

// 為向下相容，建立類別別名
// 建議直接使用 CarlLee\EcPay\Core\DTO\RqHeaderDto
class_alias(CoreRqHeaderDto::class, RqHeaderDto::class);

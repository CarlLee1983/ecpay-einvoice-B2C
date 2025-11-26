<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

/**
 * API 請求錯誤時拋出的例外。
 *
 * 用於與綠界 API 通訊過程中的錯誤，例如：
 * - 網路連線失敗
 * - HTTP 錯誤回應（403、500 等）
 * - 回應格式解析失敗
 */
class RequestException extends EcPayException
{
}


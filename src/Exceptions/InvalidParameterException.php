<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

/**
 * 參數格式或值無效時拋出的例外。
 *
 * 用於方法參數驗證錯誤，例如：
 * - 參數格式錯誤（如日期格式、統編長度）
 * - 參數值超出允許範圍
 * - 參數類型不正確
 */
class InvalidParameterException extends EcPayException
{
}

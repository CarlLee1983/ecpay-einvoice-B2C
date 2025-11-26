<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

/**
 * 資料驗證失敗時拋出的例外。
 *
 * 用於發票、折讓等業務邏輯驗證錯誤，例如：
 * - 缺少必填欄位
 * - 欄位值不符合業務規則
 * - 金額計算不一致
 */
class ValidationException extends EcPayException
{
}

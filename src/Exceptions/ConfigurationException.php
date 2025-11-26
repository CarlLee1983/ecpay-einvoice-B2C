<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

/**
 * 設定錯誤時拋出的例外。
 *
 * 用於套件設定相關錯誤，例如：
 * - MerchantID 未設定或無效
 * - 工廠別名未註冊
 * - 服務提供者配置錯誤
 */
class ConfigurationException extends EcPayException
{
}


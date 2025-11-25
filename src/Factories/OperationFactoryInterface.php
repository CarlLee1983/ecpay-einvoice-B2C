<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Factories;

use CarlLee\EcPayB2C\Content;

/**
 * 工廠介面，統一產生所有 Content 衍生操作物件。
 */
interface OperationFactoryInterface
{
    /**
     * 建立指定別名或類別的操作物件。
     *
     * @param string $target 別名（例如 invoice、queries.get_invoice）或完整類別名稱
     * @param array $parameters 自訂建構參數，預設使用工廠設定的憑證
     * @return Content
     */
    public function make(string $target, array $parameters = []): Content;

    /**
     * 自訂別名解析對應的生成邏輯。
     *
     * @param string $alias
     * @param callable $resolver
     */
    public function extend(string $alias, callable $resolver): void;

    /**
     * 自訂別名對應的實際類別。
     *
     * @param string $alias
     * @param string $class
     */
    public function alias(string $alias, string $class): void;

    /**
     * 新增共用初始化邏輯。
     *
     * @param callable $initializer
     */
    public function addInitializer(callable $initializer): void;

    /**
     * 更新工廠預設憑證。
     *
     * @param string $merchantId
     * @param string $hashKey
     * @param string $hashIV
     */
    public function setCredentials(string $merchantId, string $hashKey, string $hashIV): void;
}

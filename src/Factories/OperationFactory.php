<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Factories;

use CarlLee\EcPay\Core\AbstractOperationFactory;
use CarlLee\EcPayB2C\Content;

/**
 * B2C 電子發票操作工廠。
 *
 * 繼承自 Core 的 AbstractOperationFactory，
 * 提供 B2C 套件特定的命名空間配置。
 */
class OperationFactory extends AbstractOperationFactory implements OperationFactoryInterface
{
    /**
     * @inheritDoc
     */
    protected function getBaseNamespace(): string
    {
        return 'CarlLee\\EcPayB2C';
    }

    /**
     * @inheritDoc
     */
    protected function getContentClass(): string
    {
        return Content::class;
    }
}

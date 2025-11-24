<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Laravel\Facades;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\Factories\OperationFactoryInterface;
use ecPay\eInvoice\Laravel\Services\OperationCoordinator;
use ecPay\eInvoice\Response;
use Illuminate\Support\Facades\Facade;

/**
 * Facade：封裝查詢／驗證類別的取得方式。
 */
class EcPayQuery extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor()
    {
        return 'ecpay.factory';
    }

    /**
     * 建立查詢別名，沒有前綴時會自動補上 queries.
     *
     * @param string $alias
     * @param array $parameters
     * @return Content
     */
    public static function make(string $alias = 'queries.get_invoice', array $parameters = []): Content
    {
        $normalized = static::normalizeAlias($alias);

        return static::getFactory()->make($normalized, $parameters);
    }

    /**
     * 取得查詢發票的操作物件。
     *
     * @param array $parameters
     * @return Content
     */
    public static function invoice(array $parameters = []): Content
    {
        return static::make('queries.get_invoice', $parameters);
    }

    /**
     * 取得查詢作廢發票的操作物件。
     *
     * @param array $parameters
     * @return Content
     */
    public static function invalid(array $parameters = []): Content
    {
        return static::make('queries.get_invalid_invoice', $parameters);
    }

    /**
     * 透過協調器建立查詢後直接送出。
     *
     * @param string $alias
     * @param callable $configure
     * @param array $parameters
     */
    public static function coordinate(string $alias, callable $configure, array $parameters = []): Response
    {
        return static::getCoordinator()->dispatch(static::normalizeAlias($alias), $configure, $parameters);
    }

    /**
     * 快速查詢單筆發票（別名 queries.get_invoice）。
     *
     * @param callable $configure
     * @param array $parameters
     */
    public static function queryInvoice(callable $configure, array $parameters = []): Response
    {
        return static::coordinate('queries.get_invoice', $configure, $parameters);
    }

    /**
     * 取得工廠實體。
     *
     * @return OperationFactoryInterface
     */
    protected static function getFactory(): OperationFactoryInterface
    {
        /** @var OperationFactoryInterface $factory */
        $factory = static::getFacadeRoot();

        return $factory;
    }

    /**
     * @return OperationCoordinator
     */
    protected static function getCoordinator(): OperationCoordinator
    {
        /** @var OperationCoordinator $coordinator */
        $coordinator = static::getFacadeApplication()->make(OperationCoordinator::class);

        return $coordinator;
    }

    /**
     * 正規化查詢別名。
     */
    protected static function normalizeAlias(string $alias): string
    {
        $alias = ltrim($alias);

        if (strpos($alias, 'queries.') !== 0) {
            $alias = 'queries.' . ltrim($alias, '.');
        }

        return $alias;
    }
}

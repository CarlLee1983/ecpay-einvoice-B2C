<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Laravel\Facades;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\Factories\OperationFactoryInterface;
use ecPay\eInvoice\Laravel\Services\OperationCoordinator;
use ecPay\eInvoice\Response;
use Illuminate\Support\Facades\Facade;

/**
 * Facade：提供建立各種發票作業的便捷介面。
 */
class EcPayInvoice extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor()
    {
        return 'ecpay.factory';
    }

    /**
     * 直接建立指定別名的操作物件，預設為 Invoice。
     *
     * @param string $alias
     * @param array $parameters
     * @return Content
     */
    public static function make(string $alias = 'invoice', array $parameters = []): Content
    {
        return static::getFactory()->make($alias, $parameters);
    }

    /**
     * 取得開立發票物件。
     *
     * @param array $parameters
     * @return Content
     */
    public static function invoice(array $parameters = []): Content
    {
        return static::make('invoice', $parameters);
    }

    /**
     * 取得折讓開立物件。
     *
     * @param array $parameters
     * @return Content
     */
    public static function allowance(array $parameters = []): Content
    {
        return static::make('operations.allowance_invoice', $parameters);
    }

    /**
     * 取得作廢發票物件。
     *
     * @param array $parameters
     * @return Content
     */
    public static function invalid(array $parameters = []): Content
    {
        return static::make('operations.invalid_invoice', $parameters);
    }

    /**
     * 透過協調器建立指定別名並送出請求。
     *
     * @param string $alias
     * @param callable $configure
     * @param array $parameters
     */
    public static function coordinate(string $alias, callable $configure, array $parameters = []): Response
    {
        return static::getCoordinator()->dispatch($alias, $configure, $parameters);
    }

    /**
     * 快速開立一般發票（別名 invoice）。
     *
     * @param callable $configure
     * @param array $parameters
     */
    public static function issue(callable $configure, array $parameters = []): Response
    {
        return static::coordinate('invoice', $configure, $parameters);
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
     * 取得協調器。
     */
    protected static function getCoordinator(): OperationCoordinator
    {
        /** @var OperationCoordinator $coordinator */
        $coordinator = static::getFacadeApplication()->make(OperationCoordinator::class);

        return $coordinator;
    }
}

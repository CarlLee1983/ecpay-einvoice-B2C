<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Laravel\Facades;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\Factories\OperationFactoryInterface;
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
        if (strpos($alias, 'queries.') !== 0) {
            $alias = 'queries.' . $alias;
        }

        return static::getFactory()->make($alias, $parameters);
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
}

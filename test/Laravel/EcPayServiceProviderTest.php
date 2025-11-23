<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Tests\Laravel;

use ecPay\eInvoice\Factories\OperationFactoryInterface;
use ecPay\eInvoice\Laravel\EcPayServiceProvider;
use ecPay\eInvoice\Laravel\Facades\EcPayInvoice;
use ecPay\eInvoice\Laravel\Facades\EcPayQuery;
use ecPay\eInvoice\Operations\Invoice;
use ecPay\eInvoice\Queries\GetInvoice;
use Orchestra\Testbench\TestCase;

/**
 * 使用 Orchestra Testbench 驗證 Service Provider 綁定與 Facade。
 */
class EcPayServiceProviderTest extends TestCase
{
    /**
     * 指定要載入的套件 Service Provider。
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int,string>
     */
    protected function getPackageProviders($app)
    {
        return [
            EcPayServiceProvider::class,
        ];
    }

    /**
     * 測試前先填入必要設定。
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('ecpay-einvoice.server', 'https://einvoice-stage.ecpay.com.tw');
        $app['config']->set('ecpay-einvoice.merchant_id', '2000132');
        $app['config']->set('ecpay-einvoice.hash_key', 'HashKey');
        $app['config']->set('ecpay-einvoice.hash_iv', 'HashIV');
    }

    public function testFactoryIsRegistered(): void
    {
        $factory = $this->app->make(OperationFactoryInterface::class);

        $this->assertInstanceOf(OperationFactoryInterface::class, $factory);
        $this->assertInstanceOf(Invoice::class, $factory->make('invoice'));
    }

    public function testContainerBindings(): void
    {
        $invoice = $this->app->make('ecpay.invoice');

        $this->assertInstanceOf(Invoice::class, $invoice);
    }

    public function testFacadesResolveOperations(): void
    {
        $invoice = EcPayInvoice::make();
        $query = EcPayQuery::invoice();

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertInstanceOf(GetInvoice::class, $query);
    }
}

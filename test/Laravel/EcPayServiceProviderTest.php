<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Tests\Laravel;

use ecPay\eInvoice\EcPayClient;
use ecPay\eInvoice\Factories\OperationFactoryInterface;
use ecPay\eInvoice\Laravel\EcPayServiceProvider;
use ecPay\eInvoice\Laravel\Facades\EcPayInvoice;
use ecPay\eInvoice\Laravel\Facades\EcPayQuery;
use ecPay\eInvoice\Laravel\Services\OperationCoordinator;
use ecPay\eInvoice\Operations\Invoice;
use ecPay\eInvoice\Queries\GetInvoice;
use ecPay\eInvoice\Response;
use Mockery;
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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

    public function testCoordinatorIsRegistered(): void
    {
        $coordinator = $this->app->make(OperationCoordinator::class);

        $this->assertInstanceOf(OperationCoordinator::class, $coordinator);
    }

    public function testInvoiceFacadeIssueUsesCoordinator(): void
    {
        $response = new Response([
            'RtnCode' => 1,
            'RtnMsg' => 'OK',
        ]);

        $client = Mockery::mock(EcPayClient::class);
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::type(Invoice::class))
            ->andReturnUsing(function (Invoice $invoice) use ($response) {
                $payload = $invoice->getPayload();

                $this->assertSame('TEST-RELATE-NO', $payload['Data']['RelateNumber']);

                return $response;
            });

        $this->app->instance(EcPayClient::class, $client);
        $this->app->instance('ecpay.client', $client);
        $this->app->forgetInstance(OperationCoordinator::class);
        $this->app->forgetInstance('ecpay.coordinator');

        $result = EcPayInvoice::issue(function (Invoice $invoice) {
            $invoice->setRelateNumber('TEST-RELATE-NO')
                ->setInvoiceDate('2024-01-01')
                ->setCustomerEmail('demo@example.com')
                ->setItems([
                    [
                        'name' => 'Laravel 協調器測試',
                        'quantity' => 1,
                        'unit' => '個',
                        'price' => 100,
                    ],
                ]);
        });

        $this->assertSame($response, $result);
    }

    public function testQueryFacadeCoordinatePrefixesAlias(): void
    {
        $response = new Response(['RtnCode' => 1, 'RtnMsg' => 'OK']);
        $coordinator = Mockery::mock(OperationCoordinator::class);
        $coordinator->shouldReceive('dispatch')
            ->once()
            ->with('queries.get_invoice', Mockery::type('callable'), [])
            ->andReturn($response);

        $this->app->instance(OperationCoordinator::class, $coordinator);
        $this->app->instance('ecpay.coordinator', $coordinator);

        $returned = EcPayQuery::coordinate('get_invoice', function () {
            // 測試時僅需回傳操作物件即可
        });

        $this->assertSame($response, $returned);
    }
}

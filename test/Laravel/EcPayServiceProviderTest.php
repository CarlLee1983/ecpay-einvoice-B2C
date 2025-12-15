<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Tests\Laravel;

use CarlLee\EcPayB2C\EcPayClient;
use CarlLee\EcPayB2C\Factories\OperationFactoryInterface;
use CarlLee\EcPayB2C\Laravel\EcPayServiceProvider;
use CarlLee\EcPayB2C\Laravel\Facades\EcPayInvoice;
use CarlLee\EcPayB2C\Laravel\Facades\EcPayQuery;
use CarlLee\EcPayB2C\Laravel\Services\OperationCoordinator;
use CarlLee\EcPayB2C\Operations\Invoice;
use CarlLee\EcPayB2C\Queries\GetInvoice;
use CarlLee\EcPayB2C\Response;
use Mockery;
use Illuminate\Support\Facades\Facade;
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

    public function testFactoryKeepsPerConfigCredentials(): void
    {
        /** @var OperationFactoryInterface $defaultFactory */
        $defaultFactory = $this->app->make(OperationFactoryInterface::class);
        $defaultInvoice = $defaultFactory->make('invoice');

        $this->overrideEcPayConfig([
            'merchant_id' => 'MERCHANT-B',
            'hash_key' => 'HashKeyB',
            'hash_iv' => 'HashIVB',
        ]);

        /** @var OperationFactoryInterface $reboundFactory */
        $reboundFactory = $this->app->make(OperationFactoryInterface::class);
        $reboundInvoice = $reboundFactory->make('invoice');
        $stillOldInvoice = $defaultFactory->make('invoice');

        $this->assertNotSame($defaultFactory, $reboundFactory);
        $this->assertSame('2000132', $this->readProperty($defaultInvoice, 'merchantID'));
        $this->assertSame('HashKey', $this->readProperty($defaultInvoice, 'hashKey'));
        $this->assertSame('HashIV', $this->readProperty($defaultInvoice, 'hashIV'));
        $this->assertSame('2000132', $this->readProperty($stillOldInvoice, 'merchantID'));
        $this->assertSame('MERCHANT-B', $this->readProperty($reboundInvoice, 'merchantID'));
        $this->assertSame('HashKeyB', $this->readProperty($reboundInvoice, 'hashKey'));
        $this->assertSame('HashIVB', $this->readProperty($reboundInvoice, 'hashIV'));
    }

    public function testClientKeepsPerServerSettings(): void
    {
        /** @var EcPayClient $defaultClient */
        $defaultClient = $this->app->make(EcPayClient::class);

        $this->overrideEcPayConfig([
            'server' => 'https://einvoice-new.ecpay.com.tw',
            'hash_key' => 'HashKeyC',
            'hash_iv' => 'HashIVC',
        ]);

        /** @var EcPayClient $reboundClient */
        $reboundClient = $this->app->make(EcPayClient::class);

        $this->assertNotSame($defaultClient, $reboundClient);
        $this->assertSame(
            'https://einvoice-stage.ecpay.com.tw',
            $this->readProperty($defaultClient, 'server')
        );
        $this->assertSame('HashKey', $this->readProperty($defaultClient, 'hashKey'));
        $this->assertSame('HashIV', $this->readProperty($defaultClient, 'hashIV'));

        $this->assertSame(
            'https://einvoice-new.ecpay.com.tw',
            $this->readProperty($reboundClient, 'server')
        );
        $this->assertSame('HashKeyC', $this->readProperty($reboundClient, 'hashKey'));
        $this->assertSame('HashIVC', $this->readProperty($reboundClient, 'hashIV'));
    }

    public function testCoordinatorRebindsDependencies(): void
    {
        /** @var OperationCoordinator $originalCoordinator */
        $originalCoordinator = $this->app->make(OperationCoordinator::class);
        $originalFactory = $this->readProperty($originalCoordinator, 'factory');
        $originalClient = $this->readProperty($originalCoordinator, 'client');

        $this->overrideEcPayConfig([
            'merchant_id' => 'MERCHANT-NEW',
            'hash_key' => 'HashKeyNEW',
            'hash_iv' => 'HashIVNEW',
            'server' => 'https://einvoice-new.ecpay.com.tw',
        ]);

        $response = new Response(['RtnCode' => 1, 'RtnMsg' => 'OK']);
        $mockClient = Mockery::mock(EcPayClient::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function (Invoice $invoice) {
                return $this->readProperty($invoice, 'merchantID') === 'MERCHANT-NEW'
                    && $this->readProperty($invoice, 'hashKey') === 'HashKeyNEW'
                    && $this->readProperty($invoice, 'hashIV') === 'HashIVNEW';
            }))
            ->andReturn($response);

        $this->app->instance(EcPayClient::class, $mockClient);
        $this->app->instance('ecpay.client', $mockClient);

        /** @var OperationCoordinator $rebuiltCoordinator */
        $rebuiltCoordinator = $this->app->make(OperationCoordinator::class);
        $rebuiltFactory = $this->readProperty($rebuiltCoordinator, 'factory');
        $rebuiltClient = $this->readProperty($rebuiltCoordinator, 'client');

        $this->assertNotSame($originalCoordinator, $rebuiltCoordinator);
        $this->assertNotSame($originalFactory, $rebuiltFactory);
        $this->assertSame($mockClient, $rebuiltClient);

        $result = $rebuiltCoordinator->dispatch('invoice', function (Invoice $invoice) {
            $invoice->setRelateNumber('REBOUND-RELATE');

            return $invoice;
        });

        $this->assertSame($response, $result);
    }

    /**
     * 覆寫 config 並清除已解析的單例，確保容器會重建新的實例。
     *
     * @param array<string,mixed> $overrides
     */
    protected function overrideEcPayConfig(array $overrides): void
    {
        foreach ($overrides as $key => $value) {
            $this->app['config']->set('ecpay-einvoice.' . $key, $value);
        }

        $this->refreshEcPayBindings();
    }

    /**
     * 重置與綠界相關的單例與 Facade 快取。
     */
    protected function refreshEcPayBindings(): void
    {
        $bindings = [
            OperationFactoryInterface::class,
            'ecpay.factory',
            EcPayClient::class,
            'ecpay.client',
            OperationCoordinator::class,
            'ecpay.coordinator',
        ];

        foreach ($bindings as $binding) {
            $this->app->forgetInstance($binding);
        }

        Facade::clearResolvedInstances();
    }

    /**
     * 讀取受保護的屬性，方便驗證內部狀態。
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    protected function readProperty(object $object, string $property)
    {
        $reflection = new \ReflectionObject($object);
        if (!$reflection->hasProperty($property)) {
            $this->fail("Property {$property} not found on " . get_class($object));
        }

        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }
}

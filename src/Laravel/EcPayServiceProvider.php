<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Laravel;

use CarlLee\EcPay\Core\Laravel\AbstractEcPayServiceProvider;
use CarlLee\EcPayB2C\EcPayClient;
use CarlLee\EcPayB2C\Factories\OperationFactory;
use CarlLee\EcPayB2C\Factories\OperationFactoryInterface;
use CarlLee\EcPayB2C\Laravel\Services\OperationCoordinator;
use CarlLee\EcPayB2C\Request;
use Illuminate\Contracts\Foundation\Application;

/**
 * Laravel Service Provider：負責載入設定並綁定 Service Container。
 *
 * 繼承自 Core 的 AbstractEcPayServiceProvider，提供 B2C 特定的配置。
 *
 * 根據綠界電子發票 API 介接注意事項：
 * - 僅支援 HTTPS (443 port) 連線
 * - 支援 TLS 1.1 以上加密通訊協定
 *
 * @see https://developers.ecpay.com.tw/?p=7809
 */
class EcPayServiceProvider extends AbstractEcPayServiceProvider
{
    /**
     * @inheritDoc
     */
    protected function getConfigName(): string
    {
        return 'ecpay-einvoice';
    }

    /**
     * @inheritDoc
     */
    protected function getConfigPath(): string
    {
        return __DIR__ . '/../../config/ecpay-einvoice.php';
    }

    /**
     * @inheritDoc
     */
    protected function getServicePrefix(): string
    {
        return 'ecpay';
    }

    /**
     * @inheritDoc
     */
    protected function getFactoryInterface(): string
    {
        return OperationFactoryInterface::class;
    }

    /**
     * @inheritDoc
     */
    protected function configureRequest(): void
    {
        $verifySsl = $this->config('verify_ssl', true);
        Request::setVerifySsl((bool) $verifySsl);
    }

    /**
     * @inheritDoc
     */
    protected function registerFactory(): void
    {
        $this->app->singleton(OperationFactoryInterface::class, function (Application $app) {
            $config = $app['config']->get('ecpay-einvoice', []);
            $factoryConfig = $config['factory'] ?? [];

            $factory = new OperationFactory([
                'merchant_id' => (string) ($config['merchant_id'] ?? ''),
                'hash_key' => (string) ($config['hash_key'] ?? ''),
                'hash_iv' => (string) ($config['hash_iv'] ?? ''),
                'aliases' => $factoryConfig['aliases'] ?? [],
            ]);

            foreach ($factoryConfig['initializers'] ?? [] as $initializer) {
                $callable = $this->resolveInitializer($initializer, $app);
                if ($callable !== null) {
                    $factory->addInitializer($callable);
                }
            }

            return $factory;
        });

        $this->app->alias(OperationFactoryInterface::class, 'ecpay.factory');
    }

    /**
     * @inheritDoc
     */
    protected function registerClient(): void
    {
        $this->app->singleton(EcPayClient::class, function (Application $app) {
            $config = $app['config']->get('ecpay-einvoice', []);

            return new EcPayClient(
                (string) ($config['server'] ?? ''),
                (string) ($config['hash_key'] ?? ''),
                (string) ($config['hash_iv'] ?? '')
            );
        });

        $this->app->alias(EcPayClient::class, 'ecpay.client');
    }

    /**
     * @inheritDoc
     */
    protected function registerCoordinator(): void
    {
        $this->app->singleton(OperationCoordinator::class, function (Application $app) {
            return new OperationCoordinator(
                $app->make(OperationFactoryInterface::class),
                $app->make(EcPayClient::class)
            );
        });

        $this->app->alias(OperationCoordinator::class, 'ecpay.coordinator');
    }
}

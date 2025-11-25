<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Laravel;

use CarlLee\EcPayB2C\EcPayClient;
use CarlLee\EcPayB2C\Factories\OperationFactory;
use CarlLee\EcPayB2C\Factories\OperationFactoryInterface;
use CarlLee\EcPayB2C\Laravel\Services\OperationCoordinator;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel Service Provider：負責載入設定並綁定 Service Container。
 */
class EcPayServiceProvider extends ServiceProvider
{
    /**
     * 註冊服務。
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/ecpay-einvoice.php', 'ecpay-einvoice');

        $this->registerFactory();
        $this->registerClient();
        $this->registerOperationBindings();
        $this->registerCoordinator();
    }

    /**
     * 啟動服務（提供 config publish）。
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/ecpay-einvoice.php' => $this->configPath('ecpay-einvoice.php'),
            ], 'ecpay-einvoice-config');
        }
    }

    /**
     * 綁定工廠實例。
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
     * 綁定 EcPayClient。
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
     * 綁定協調器。
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

    /**
     * 將設定檔內的便利別名註冊至容器。
     */
    protected function registerOperationBindings(): void
    {
        $bindings = $this->app['config']->get('ecpay-einvoice.bindings', []);

        foreach ($bindings as $name => $alias) {
            $serviceId = strpos($name, 'ecpay.') === 0 ? $name : 'ecpay.' . $name;

            $this->app->bind($serviceId, function (Application $app) use ($alias) {
                /** @var OperationFactoryInterface $factory */
                $factory = $app->make(OperationFactoryInterface::class);

                return $factory->make((string) $alias);
            });
        }
    }

    /**
     * 將設定值轉為可呼叫的初始化邏輯。
     *
     * @param mixed $initializer
     * @param Application $app
     * @return callable|null
     */
    protected function resolveInitializer($initializer, Application $app): ?callable
    {
        if (is_string($initializer) && class_exists($initializer)) {
            $callable = $app->make($initializer);
            if (is_callable($callable)) {
                return $callable;
            }
        }

        if (is_callable($initializer)) {
            return $initializer;
        }

        return null;
    }

    /**
     * 取得 config 儲存路徑，在非 Laravel 環境提供後援。
     *
     * @param string $file
     * @return string
     */
    protected function configPath(string $file): string
    {
        if (function_exists('config_path')) {
            return config_path($file);
        }

        return $this->app->basePath('config/' . $file);
    }
}

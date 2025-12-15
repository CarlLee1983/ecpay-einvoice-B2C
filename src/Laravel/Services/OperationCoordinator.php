<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Laravel\Services;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\EcPayClient;
use CarlLee\EcPayB2C\Contracts\SendableCommandInterface;
use CarlLee\EcPayB2C\Factories\OperationFactoryInterface;
use CarlLee\EcPayB2C\Response;
use InvalidArgumentException;

/**
 * 封裝 Laravel 端統一流程：取得 operation、設定欄位、送出並解析 Response。
 */
class OperationCoordinator
{
    /**
     * @param OperationFactoryInterface $factory
     * @param EcPayClient $client
     */
    public function __construct(
        private readonly OperationFactoryInterface $factory,
        private readonly EcPayClient $client,
    ) {
    }

    /**
     * 依別名建立操作物件、執行設定回呼，並送出請求。
     *
     * @param string $alias
     * @param array $parameters
     * @param callable|null $configure 接收 Content 的回呼，可回傳 Content 以覆寫實體。
     * @return Response
     */
    public function dispatch(string $alias, ?callable $configure = null, array $parameters = []): Response
    {
        $operation = $this->factory->make($alias, $parameters);

        if ($configure !== null) {
            $result = $configure($operation);

            if ($result instanceof Content) {
                $operation = $result;
            } elseif ($result !== null && !$result instanceof Content) {
                throw new InvalidArgumentException('協調器回呼必須回傳 Content 或 null。');
            }
        }

        return $this->client->send($operation);
    }

    /**
     * 直接發送已建構完成的命令。
     *
     * @param Content&SendableCommandInterface $command
     * @param callable|null $configure
     * @return Response
     */
    public function send(Content&SendableCommandInterface $command, ?callable $configure = null): Response
    {
        if ($configure !== null) {
            $result = $configure($command);
            if ($result instanceof Content) {
                $command = $result;
            }
        }

        return $this->client->send($command);
    }

    /**
     * 只建立 operation，交由呼叫端自行處理。
     *
     * @param string $alias
     * @param array $parameters
     * @return Content
     */
    public function make(string $alias, array $parameters = []): Content
    {
        return $this->factory->make($alias, $parameters);
    }
}

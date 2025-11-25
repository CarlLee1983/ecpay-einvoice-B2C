<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Factories;

use CarlLee\EcPayB2C\Content;
use InvalidArgumentException;

/**
 * 工廠實作：集中產生 Operations/Queries/Notifications/Printing 物件。
 */
class OperationFactory implements OperationFactoryInterface
{
    /**
     * Content 相關類別的基底命名空間。
     */
    private const BASE_NAMESPACE = 'CarlLee\\EcPayB2C';

    /**
     * 未指定群組時預設使用 Operations。
     */
    private const DEFAULT_GROUP = 'Operations';

    /**
     * 預設支援的群組別名對應。
     */
    private const GROUP_MAP = [
        'operations' => 'Operations',
        'operation' => 'Operations',
        'ops' => 'Operations',
        'queries' => 'Queries',
        'query' => 'Queries',
        'notifications' => 'Notifications',
        'notification' => 'Notifications',
        'notify' => 'Notifications',
        'printing' => 'Printing',
        'print' => 'Printing',
    ];

    /**
     * 需要保留原樣（大寫）的特殊片段。
     *
     * @var array<string,string>
     */
    private array $specialWords = [
        'id' => 'ID',
    ];

    /**
     * 商店憑證設定。
     *
     * @var array{merchant_id:string,hash_key:string,hash_iv:string}
     */
    private array $credentials = [
        'merchant_id' => '',
        'hash_key' => '',
        'hash_iv' => '',
    ];

    /**
     * 自訂別名對應的實際類別。
     *
     * @var array<string,string>
     */
    private array $aliases = [];

    /**
     * 自訂生成器。
     *
     * @var array<string,callable>
     */
    private array $resolvers = [];

    /**
     * 共用初始化程式。
     *
     * @var callable[]
     */
    private array $initializers = [];

    /**
     * @param array{
     *     merchant_id?:string,
     *     hash_key?:string,
     *     hash_iv?:string,
     *     aliases?:array<string,string>,
     *     resolvers?:array<string,callable>,
     *     initializers?:callable[]
     * } $config
     */
    public function __construct(array $config = [])
    {
        $this->setCredentials(
            (string) ($config['merchant_id'] ?? ''),
            (string) ($config['hash_key'] ?? ''),
            (string) ($config['hash_iv'] ?? '')
        );

        foreach ($config['aliases'] ?? [] as $alias => $class) {
            $this->alias($alias, $class);
        }

        foreach ($config['resolvers'] ?? [] as $alias => $resolver) {
            $this->extend($alias, $resolver);
        }

        foreach ($config['initializers'] ?? [] as $initializer) {
            $this->addInitializer($initializer);
        }
    }

    /**
     * @inheritDoc
     */
    public function make(string $target, array $parameters = []): Content
    {
        $key = $this->normalizeKey($target);

        if (isset($this->resolvers[$key])) {
            $content = $this->resolvers[$key]($parameters, $this);

            if (!$content instanceof Content) {
                throw new InvalidArgumentException("自訂解析 {$target} 必須回傳 Content 衍生類別。");
            }

            return $this->initialize($content);
        }

        $class = $this->resolveClassName($target, $key);

        $instance = $this->buildInstance($class, $parameters);

        return $this->initialize($instance);
    }

    /**
     * @inheritDoc
     */
    public function extend(string $alias, callable $resolver): void
    {
        $this->resolvers[$this->normalizeKey($alias)] = $resolver;
    }

    /**
     * @inheritDoc
     */
    public function alias(string $alias, string $class): void
    {
        $key = $this->normalizeKey($alias);
        $this->aliases[$key] = $class;
    }

    /**
     * @inheritDoc
     */
    public function addInitializer(callable $initializer): void
    {
        $this->initializers[] = $initializer;
    }

    /**
     * @inheritDoc
     */
    public function setCredentials(string $merchantId, string $hashKey, string $hashIV): void
    {
        $this->credentials = [
            'merchant_id' => $merchantId,
            'hash_key' => $hashKey,
            'hash_iv' => $hashIV,
        ];
    }

    /**
     * 建立實際物件。
     *
     * @param class-string<Content> $class
     * @param array<int,mixed> $parameters
     * @return Content
     */
    protected function buildInstance(string $class, array $parameters): Content
    {
        if (!is_subclass_of($class, Content::class)) {
            throw new InvalidArgumentException("{$class} 必須繼承 " . Content::class);
        }

        if (empty($parameters)) {
            $parameters = [
                $this->credentials['merchant_id'],
                $this->credentials['hash_key'],
                $this->credentials['hash_iv'],
            ];
        }

        /** @var Content $instance */
        $instance = new $class(...array_values($parameters));

        return $instance;
    }

    /**
     * 執行所有初始化程式。
     *
     * @param Content $content
     * @return Content
     */
    protected function initialize(Content $content): Content
    {
        foreach ($this->initializers as $initializer) {
            $initializer($content);
        }

        return $content;
    }

    /**
     * 解析別名並取得實際類別名稱。
     *
     * @param string $target
     * @return class-string
     */
    protected function resolveClassName(string $target, ?string $normalized = null): string
    {
        $key = $normalized ?? $this->normalizeKey($target);

        if (isset($this->aliases[$key])) {
            $class = $this->aliases[$key];
            if (!class_exists($class)) {
                throw new InvalidArgumentException("別名 {$key} 指向的類別 {$class} 不存在。");
            }

            return $class;
        }

        $trimmed = trim($target);
        $classCandidate = ltrim($trimmed, '\\');

        if ($classCandidate !== '' && class_exists($classCandidate)) {
            return $classCandidate;
        }

        if ($classCandidate !== '' && class_exists('\\' . $classCandidate)) {
            return '\\' . $classCandidate;
        }

        [$group, $name] = $this->parseAlias($key);
        $class = sprintf('%s\\%s\\%s', self::BASE_NAMESPACE, $group, $this->studly($name));

        if (!class_exists($class)) {
            throw new InvalidArgumentException("找不到 {$target} 對應的類別 {$class}。");
        }

        return $class;
    }

    /**
     * 分析別名，回傳群組與名稱。
     *
     * @param string $alias
     * @return array{string,string}
     */
    protected function parseAlias(string $alias): array
    {
        if ($alias === '') {
            throw new InvalidArgumentException('別名不得為空字串。');
        }

        if (strpos($alias, '.') === false) {
            return [self::DEFAULT_GROUP, $alias];
        }

        [$groupKey, $name] = explode('.', $alias, 2);
        $groupKey = strtolower($groupKey);
        $group = self::GROUP_MAP[$groupKey] ?? self::DEFAULT_GROUP;

        if ($name === '') {
            throw new InvalidArgumentException('別名需包含實際類別名稱。');
        }

        return [$group, $name];
    }

    /**
     * 將字串轉為 StudlyCase，並保留特殊片段。
     *
     * @param string $value
     * @return string
     */
    protected function studly(string $value): string
    {
        $value = str_replace('.', '_', $value);
        $segments = preg_split('/[^a-zA-Z0-9]+/', $value) ?: [];
        $studly = '';

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            $lower = strtolower($segment);
            if (isset($this->specialWords[$lower])) {
                $studly .= $this->specialWords[$lower];
                continue;
            }

            $studly .= ucfirst($lower);
        }

        return $studly;
    }

    /**
     * 正規化 key，方便儲存。
     *
     * @param string $value
     * @return string
     */
    protected function normalizeKey(string $value): string
    {
        return strtolower(trim($value));
    }
}

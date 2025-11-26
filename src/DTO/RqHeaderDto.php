<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\DTO;

use InvalidArgumentException;

/**
 * 代表 RqHeader 結構的 Value Object。
 */
final class RqHeaderDto
{
    /**
     * @var int
     */
    private int $timestamp;

    /**
     * __construct
     *
     * @param int|null $timestamp
     */
    public function __construct(?int $timestamp = null)
    {
        $this->setTimestamp($timestamp ?? time());
    }

    /**
     * 建立 DTO。
     *
     * @param array<string,mixed> $header
     */
    public static function fromArray(array $header): self
    {
        if (!isset($header['Timestamp'])) {
            throw new InvalidArgumentException('RqHeader timestamp is required.');
        }

        return new self((int) $header['Timestamp']);
    }

    /**
     * 設定時間戳。
     *
     * @param int $timestamp
     * @return self
     */
    public function setTimestamp(int $timestamp): self
    {
        if ($timestamp <= 0) {
            throw new InvalidArgumentException('RqHeader timestamp must be greater than 0.');
        }

        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * 取得時間戳。
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * 轉換為 payload。
     *
     * @return array<string,int>
     */
    public function toPayload(): array
    {
        return [
            'Timestamp' => $this->timestamp,
        ];
    }
}

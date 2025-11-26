<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

use CarlLee\EcPayB2C\Exceptions\ApiException;

class Response
{
    /**
     * Response data.
     *
     * @var array<string, mixed>
     */
    protected array $data = [
        'RtnCode' => 0,
        'RtnMsg' => '',
    ];

    /**
     * __construct
     *
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->setData($data);
        }
    }

    /**
     * Setting data.
     *
     * @param array<string, mixed> $data
     * @return static
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Response success.
     */
    public function success(): bool
    {
        return $this->data['RtnCode'] == 1;
    }

    /**
     * Alias for success().
     */
    public function isSuccess(): bool
    {
        return $this->success();
    }

    /**
     * Check if response is an error.
     */
    public function isError(): bool
    {
        return !$this->success();
    }

    /**
     * Get response message.
     */
    public function getMessage(): string
    {
        return $this->data['RtnMsg'] ?? '';
    }

    /**
     * Get response code.
     */
    public function getCode(): int
    {
        return (int) ($this->data['RtnCode'] ?? 0);
    }

    /**
     * Get response data.
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Convert response to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Throw exception if response is an error.
     *
     * @throws ApiException
     * @return static
     */
    public function throw(): static
    {
        if ($this->isError()) {
            throw ApiException::fromResponse(
                $this->getCode(),
                $this->getMessage(),
                $this->data
            );
        }

        return $this;
    }
}

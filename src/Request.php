<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

use CarlLee\EcPayB2C\Exceptions\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * HTTP 請求處理類別。
 *
 * 根據綠界電子發票 API 介接注意事項：
 * - 僅支援 HTTPS (443 port) 連線
 * - 使用 HTTP POST 方式傳送
 * - 支援 TLS 1.1 以上加密通訊協定
 *
 * @see https://developers.ecpay.com.tw/?p=7809
 */
class Request
{
    /**
     * 最低支援的 TLS 版本（TLS 1.1）。
     */
    public const int MIN_TLS_VERSION = CURL_SSLVERSION_TLSv1_1;

    /**
     * The request url.
     *
     * @var string
     */
    protected string $url = '';

    /**
     * The request body content.
     *
     * @var array<string, mixed>
     */
    protected array $content = [];

    /**
     * The HTTP client instance.
     *
     * @var Client|null
     */
    protected static ?Client $client = null;

    /**
     * 是否啟用 SSL 驗證（正式環境建議啟用）。
     *
     * @var bool
     */
    protected static bool $verifySsl = true;

    /**
     * Set HTTP client instance.
     *
     * @param Client|null $client
     */
    public static function setHttpClient(?Client $client): void
    {
        self::$client = $client;
    }

    /**
     * 設定是否啟用 SSL 驗證。
     *
     * @param bool $verify
     */
    public static function setVerifySsl(bool $verify): void
    {
        self::$verifySsl = $verify;
    }

    /**
     * __construct
     *
     * @param string $url
     * @param array<string, mixed> $content
     */
    public function __construct(string $url = '', array $content = [])
    {
        $this->url = $url;
        $this->content = $content;
    }

    /**
     * Send request to ecpay server.
     *
     * 使用 HTTP POST 方式傳送至綠界 API，
     * 並確保使用 TLS 1.1 以上的加密通訊協定。
     *
     * @param string $url
     * @param array<string, mixed> $content
     * @throws ApiException
     * @return array<string, mixed>
     */
    public function send(string $url = '', array $content = []): array
    {
        try {
            if (!self::$client) {
                self::$client = $this->createDefaultClient();
            }

            $sendContent = $content ?: $this->content;
            $response = self::$client->request(
                'POST',
                $url ?: $this->url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($sendContent),
                ]
            );

            return json_decode((string) $response->getBody(), true);
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                $response = $exception->getResponse();

                throw ApiException::requestFailed(
                    $response->getBody()->getContents(),
                    $exception
                );
            }

            throw ApiException::requestFailed($exception->getMessage(), $exception);
        }
    }

    /**
     * 建立預設的 HTTP Client，符合綠界 API 介接規範。
     *
     * @return Client
     */
    protected function createDefaultClient(): Client
    {
        return new Client([
            'verify' => self::$verifySsl,
            'curl' => [
                CURLOPT_SSLVERSION => self::MIN_TLS_VERSION,
            ],
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }
}

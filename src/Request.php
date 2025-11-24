<?php

declare(strict_types=1);

namespace ecPay\eInvoice;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Request
{
    /**
     * The request url.
     *
     * @var string
     */
    protected $url = '';

    /**
     * The request body content.
     *
     * @var array
     */
    protected $content = [];

    /**
     * The HTTP client instance.
     *
     * @var Client
     */
    protected static $client;

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
     * __construct
     *
     * @param string $url
     * @param array $content
     */
    public function __construct(string $url = '', array $content = [])
    {
        $this->url = $url;
        $this->content = $content;
    }

    /**
     * Send request to ecpay server.
     *
     * @param string $url
     * @param array $content
     * @throws Exception
     * @return array
     */
    public function send(string $url = '', array $content = []): array
    {
        try {
            if (!self::$client) {
                self::$client = new Client(['verify' => false]);
            }

            $sendContent = $content ?: $this->content;
            $response = self::$client->request(
                'POST',
                $url ?: $this->url,
                ['body' => json_encode($sendContent)]
            );

            return json_decode((string) $response->getBody(), true);
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                $response = $exception->getResponse();

                throw new Exception($response->getBody()->getContents());
            }

            throw new Exception('Request Error: ' . $exception->getMessage());
        }
    }
}

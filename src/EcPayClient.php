<?php

declare(strict_types=1);

namespace ecPay\eInvoice;

use Exception;

class EcPayClient
{
    use AES;

    /**
     * The request server.
     *
     * @var string
     */
    protected $requestServer = '';

    /**
     * Hash key.
     *
     * @var string
     */
    protected $hashKey = '';

    /**
     * Hash IV.
     *
     * @var string
     */
    protected $hashIV = '';

    /**
     * __construct
     *
     * @param string $server
     * @param string $hashKey
     * @param string $hashIV
     */
    public function __construct(string $server, string $hashKey, string $hashIV)
    {
        $this->requestServer = $server;
        $this->hashKey = $hashKey;
        $this->hashIV = $hashIV;
    }

    /**
     * Send request to ECPay.
     *
     * @param Content $invoice
     * @return Response
     * @throws Exception
     */
    public function send(Content $invoice): Response
    {
        // Ensure the invoice has the necessary credentials from the client
        $invoice->setHashKey($this->hashKey);
        $invoice->setHashIV($this->hashIV);
        // Note: MerchantID is part of the content, but usually set in invoice.
        // We could enforce it here if we moved MerchantID to Client, but sticking to minimal changes.

        $payload = $invoice->getContent();
        $requestPath = $invoice->getRequestPath();

        $body = (new Request($this->requestServer . $requestPath, $payload))->send();

        $response = new Response();

        if (!empty($body['Data'])) {
            $body['Data'] = $this->decrypt($body['Data']);
            $decodedData = json_decode($body['Data'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('The response data format is invalid.');
            }

            $response->setData($decodedData);
        } else {
            $data = [
                'RtnCode' => $body['TransCode'],
                'RtnMsg' => $body['TransMsg'],
            ];
            $response->setData($data);
        }

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

use CarlLee\EcPayB2C\Contracts\CommandInterface;
use CarlLee\EcPayB2C\Exceptions\ApiException;
use CarlLee\EcPayB2C\Exceptions\EcPayException;
use CarlLee\EcPayB2C\Infrastructure\CipherService;
use CarlLee\EcPayB2C\Infrastructure\PayloadEncoder;

class EcPayClient
{
    /**
     * The request server.
     */
    protected string $requestServer = '';

    /**
     * Hash key.
     */
    protected string $hashKey = '';

    /**
     * Hash IV.
     */
    protected string $hashIV = '';

    /**
     * Cipher service for encryption/decryption.
     */
    protected CipherService $cipherService;

    /**
     * Payload encoder for encoding/decoding.
     */
    protected PayloadEncoder $payloadEncoder;

    /**
     * __construct
     *
     * @param string $server
     * @param string $hashKey
     * @param string $hashIV
     * @param PayloadEncoder|null $payloadEncoder
     */
    public function __construct(
        string $server,
        string $hashKey,
        string $hashIV,
        ?PayloadEncoder $payloadEncoder = null
    ) {
        $this->requestServer = $server;
        $this->hashKey = $hashKey;
        $this->hashIV = $hashIV;

        $this->cipherService = new CipherService($hashKey, $hashIV);
        $this->payloadEncoder = $payloadEncoder ?: new PayloadEncoder($this->cipherService);
    }

    /**
     * Send request to ECPay.
     *
     * @param CommandInterface $command
     * @return Response
     * @throws EcPayException
     * @throws ApiException
     */
    public function send(CommandInterface $command): Response
    {
        // 將金鑰同步給命令，以保留既有運作方式
        $command->setHashKey($this->hashKey);
        $command->setHashIV($this->hashIV);

        $payload = $command->getPayload();
        $requestPath = $command->getRequestPath();
        $payloadEncoder = $command->getPayloadEncoder();
        $transportBody = $payloadEncoder->encodePayload($payload);

        $body = (new Request($this->requestServer . $requestPath, $transportBody))->send();

        $response = new Response();

        if (!empty($body['Data'])) {
            $decodedData = $payloadEncoder->decodeData($body['Data']);
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

<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

use CarlLee\EcPay\Core\Contracts\PayloadEncoderInterface;
use CarlLee\EcPayB2C\Contracts\SendableCommandInterface;
use CarlLee\EcPayB2C\Exceptions\ApiException;
use CarlLee\EcPayB2C\Exceptions\EcPayException;

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
     * __construct
     *
     * @param string $server
     * @param string $hashKey
     * @param string $hashIV
     */
    public function __construct(
        string $server,
        string $hashKey,
        string $hashIV
    ) {
        $this->requestServer = $server;
        $this->hashKey = $hashKey;
        $this->hashIV = $hashIV;
    }

    /**
     * Send request to ECPay.
     *
     * @param SendableCommandInterface $command
     * @return Response
     * @throws EcPayException
     * @throws ApiException
     */
    public function send(SendableCommandInterface $command): Response
    {
        // 將金鑰同步給命令，以保留既有運作方式
        $command->setHashKey($this->hashKey);
        $command->setHashIV($this->hashIV);

        $requestPath = $command->getRequestPath();
        $payloadEncoder = $command->getPayloadEncoder();
        $transportBody = $command->getContent();

        return $this->sendRaw($command, $requestPath, $payloadEncoder, $transportBody);
    }

    /**
     * Send request to ECPay (encryptable command).
     *
     * @param SendableCommandInterface $command
     * @return Response
     * @throws EcPayException
     * @throws ApiException
     *
     * @deprecated since 4.1.1 Use `send()` instead.
     */
    public function sendEncrypted(SendableCommandInterface $command): Response
    {
        return $this->send($command);
    }

    /**
     * @param array<string, mixed> $transportBody
     */
    private function sendRaw(
        SendableCommandInterface $command,
        string $requestPath,
        PayloadEncoderInterface $payloadEncoder,
        array $transportBody
    ): Response {
        $body = (new Request($this->requestServer . $requestPath, $transportBody))->send();

        $response = new Response();
        $response->setData($command->decodeResponse($body, $payloadEncoder));

        return $response;
    }
}

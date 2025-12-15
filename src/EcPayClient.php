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
     * __construct
     *
     * @param string $server The request server.
     * @param string $hashKey The hash key.
     * @param string $hashIV The hash IV.
     */
    public function __construct(
        protected string $server,
        protected string $hashKey,
        protected string $hashIV
    ) {
    }

    /**
     * Send request to ECPay.
     *
     * @param SendableCommandInterface $command The command to send.
     * @return Response The response from ECPay.
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
     * Send request to ECPay (raw).
     *
     * @param SendableCommandInterface $command The command to send.
     * @param string $requestPath The request path.
     * @param PayloadEncoderInterface $payloadEncoder The payload encoder.
     * @param array<string, mixed> $transportBody The transport body.
     * @return Response The response from ECPay.
     */
    private function sendRaw(
        SendableCommandInterface $command,
        string $requestPath,
        PayloadEncoderInterface $payloadEncoder,
        array $transportBody
    ): Response {
        $body = (new Request($this->server . $requestPath, $transportBody))->send();

        $response = new Response();
        $response->setData($command->decodeResponse($body, $payloadEncoder));

        return $response;
    }
}

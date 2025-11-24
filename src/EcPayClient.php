<?php

declare(strict_types=1);

namespace ecPay\eInvoice;

use ecPay\eInvoice\Contracts\CommandInterface;
use ecPay\eInvoice\Infrastructure\CipherService;
use ecPay\eInvoice\Infrastructure\PayloadEncoder;
use Exception;

class EcPayClient
{
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
     * @var CipherService
     */
    protected $cipherService;

    /**
     * @var PayloadEncoder
     */
    protected $payloadEncoder;

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
     * @throws Exception
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
            $decodedData = $this->payloadEncoder->decodeData($body['Data']);
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

<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Infrastructure;

use Exception;

/**
 * 將領域資料轉換為傳輸格式並提供解碼功能。
 */
class PayloadEncoder
{
    /**
     * @var CipherService
     */
    private $cipherService;

    /**
     * __construct
     *
     * @param CipherService $cipherService
     */
    public function __construct(CipherService $cipherService)
    {
        $this->cipherService = $cipherService;
    }

    /**
     * 將內容轉成 ECPay 要求的傳輸格式。
     *
     * @param array $payload
     * @throws Exception
     * @return array
     */
    public function encodePayload(array $payload): array
    {
        if (!isset($payload['Data'])) {
            throw new Exception('The payload structure is invalid.');
        }

        $encodedData = json_encode($payload['Data']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('The invoice data format is invalid.');
        }

        $encodedData = urlencode($encodedData);
        $encodedData = $this->transUrlencode($encodedData);

        $payload['Data'] = $this->cipherService->encrypt($encodedData);

        return $payload;
    }

    /**
     * 將回傳的 Data 還原為陣列欄位。
     *
     * @param string $encryptedData
     * @throws Exception
     * @return array
     */
    public function decodeData(string $encryptedData): array
    {
        $decrypted = $this->cipherService->decrypt($encryptedData);
        $decoded = json_decode(urldecode($decrypted), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('The response data format is invalid.');
        }

        return $decoded;
    }

    /**
     * 與 .NET 相容的 URL encode 轉換。
     *
     * @param string $param
     * @throws Exception
     * @return string
     */
    private function transUrlencode(string $param): string
    {
        $search = ['%2d', '%5f', '%2e', '%21', '%2a', '%28', '%29'];
        $replace = ['-', '_', '.', '!', '*', '(', ')'];

        return str_replace($search, $replace, $param);
    }
}

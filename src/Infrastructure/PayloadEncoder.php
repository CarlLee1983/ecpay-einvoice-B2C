<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Infrastructure;

use CarlLee\EcPayB2C\Exceptions\ApiException;
use CarlLee\EcPayB2C\Exceptions\PayloadException;

/**
 * 將領域資料轉換為傳輸格式並提供解碼功能。
 */
class PayloadEncoder
{
    /**
     * __construct
     *
     * @param CipherService $cipherService
     */
    public function __construct(
        private readonly CipherService $cipherService,
    ) {
    }

    /**
     * 將內容轉成 ECPay 要求的傳輸格式。
     *
     * @param array<string, mixed> $payload
     * @throws PayloadException
     * @return array<string, mixed>
     */
    public function encodePayload(array $payload): array
    {
        if (!isset($payload['Data'])) {
            throw PayloadException::missingData();
        }

        $encodedData = json_encode($payload['Data']);
        if ($encodedData === false) {
            throw PayloadException::invalidData('JSON 編碼失敗');
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
     * @throws ApiException
     * @return array<string, mixed>
     */
    public function decodeData(string $encryptedData): array
    {
        $decrypted = $this->cipherService->decrypt($encryptedData);
        $urlDecoded = urldecode($decrypted);

        // PHP 8.3: 使用 json_validate() 先驗證 JSON 格式
        if (!json_validate($urlDecoded)) {
            throw ApiException::invalidResponse('回應資料 JSON 格式無效');
        }

        return json_decode($urlDecoded, true);
    }

    /**
     * 與 .NET 相容的 URL encode 轉換。
     *
     * @param string $param
     * @return string
     */
    private function transUrlencode(string $param): string
    {
        $search = ['%2d', '%5f', '%2e', '%21', '%2a', '%28', '%29'];
        $replace = ['-', '_', '.', '!', '*', '(', ')'];

        return str_replace($search, $replace, $param);
    }
}

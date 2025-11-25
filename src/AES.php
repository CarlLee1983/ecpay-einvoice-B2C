<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

use CarlLee\EcPayB2C\Infrastructure\CipherService;
use Exception;

/**
 * AES encryption and decryption.
 *
 * @deprecated 改以 Infrastructure\CipherService 直接注入，僅保留相容性。
 */
trait AES
{
    /**
     * 透過 CipherService 進行加密。
     */
    protected function encrypt(string $data): string
    {
        return $this->createCipherService()->encrypt($data);
    }

    /**
     * 透過 CipherService 進行解密，並與舊行為一致將結果做 urldecode。
     */
    public function decrypt(string $data): string
    {
        $decrypted = $this->createCipherService()->decrypt($data);

        return \urldecode($decrypted);
    }

    /**
     * 建立 CipherService 實例，確保金鑰存在。
     */
    private function createCipherService(): CipherService
    {
        if (!property_exists($this, 'hashKey') || empty($this->hashKey)) {
            throw new Exception('HashKey is empty.');
        }

        if (!property_exists($this, 'hashIV') || empty($this->hashIV)) {
            throw new Exception('HashIV is empty.');
        }

        return new CipherService($this->hashKey, $this->hashIV);
    }
}

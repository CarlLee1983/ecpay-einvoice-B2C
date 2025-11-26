<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Infrastructure;

use CarlLee\EcPayB2C\Exceptions\EncryptionException;

/**
 * 專責處理 AES 加解密。
 */
class CipherService
{
    /**
     * @var string
     */
    private string $hashKey;

    /**
     * @var string
     */
    private string $hashIV;

    /**
     * __construct
     *
     * @param string $hashKey
     * @param string $hashIV
     * @throws EncryptionException
     */
    public function __construct(string $hashKey, string $hashIV)
    {
        if ($hashKey === '') {
            throw EncryptionException::invalidKey('HashKey');
        }

        if ($hashIV === '') {
            throw EncryptionException::invalidKey('HashIV');
        }

        $this->hashKey = $hashKey;
        $this->hashIV = $hashIV;
    }

    /**
     * 進行 AES/CBC/PKCS7 加密。
     *
     * @param string $data
     * @throws EncryptionException
     * @return string
     */
    public function encrypt(string $data): string
    {
        $encrypted = \openssl_encrypt(
            $data,
            'AES-128-CBC',
            $this->hashKey,
            OPENSSL_RAW_DATA,
            $this->hashIV
        );

        if ($encrypted === false) {
            throw EncryptionException::encryptionFailed();
        }

        return \base64_encode($encrypted);
    }

    /**
     * 進行 AES/CBC/PKCS7 解密。
     *
     * @param string $data
     * @throws EncryptionException
     * @return string
     */
    public function decrypt(string $data): string
    {
        if ($data === '') {
            throw EncryptionException::decryptionFailed('資料為空');
        }

        $decoded = \base64_decode($data, true);
        if ($decoded === false) {
            throw EncryptionException::decryptionFailed('Base64 解碼失敗');
        }

        $decrypted = \openssl_decrypt(
            $decoded,
            'AES-128-CBC',
            $this->hashKey,
            OPENSSL_RAW_DATA,
            $this->hashIV
        );

        if ($decrypted === false) {
            throw EncryptionException::decryptionFailed('AES 解密失敗');
        }

        return $decrypted;
    }
}

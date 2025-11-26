<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Infrastructure;

use CarlLee\EcPayB2C\Exceptions\ConfigurationException;
use CarlLee\EcPayB2C\Exceptions\EncryptionException;

/**
 * 專責處理 AES 加解密。
 */
class CipherService
{
    /**
     * __construct
     *
     * @param string $hashKey
     * @param string $hashIV
     */
    public function __construct(
        private readonly string $hashKey,
        private readonly string $hashIV,
    ) {
        if ($hashKey === '') {
            throw new ConfigurationException('HashKey is empty.');
        }

        if ($hashIV === '') {
            throw new ConfigurationException('HashIV is empty.');
        }
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
            throw new EncryptionException('Encryption failed.');
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
            throw new EncryptionException('Decryption failed.');
        }

        $decoded = \base64_decode($data, true);
        if ($decoded === false) {
            throw new EncryptionException('Decryption failed.');
        }

        $decrypted = \openssl_decrypt(
            $decoded,
            'AES-128-CBC',
            $this->hashKey,
            OPENSSL_RAW_DATA,
            $this->hashIV
        );

        if ($decrypted === false) {
            throw new EncryptionException('Decryption failed.');
        }

        return $decrypted;
    }
}

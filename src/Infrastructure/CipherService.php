<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Infrastructure;

use Exception;

/**
 * 專責處理 AES 加解密。
 */
class CipherService
{
    /**
     * @var string
     */
    private $hashKey;

    /**
     * @var string
     */
    private $hashIV;

    /**
     * __construct
     *
     * @param string $hashKey
     * @param string $hashIV
     */
    public function __construct(string $hashKey, string $hashIV)
    {
        if ($hashKey === '') {
            throw new Exception('HashKey is empty.');
        }

        if ($hashIV === '') {
            throw new Exception('HashIV is empty.');
        }

        $this->hashKey = $hashKey;
        $this->hashIV = $hashIV;
    }

    /**
     * 進行 AES/CBC/PKCS7 加密。
     *
     * @param string $data
     * @throws Exception
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
            throw new Exception('Encryption failed.');
        }

        return \base64_encode($encrypted);
    }

    /**
     * 進行 AES/CBC/PKCS7 解密。
     *
     * @param string $data
     * @throws Exception
     * @return string
     */
    public function decrypt(string $data): string
    {
        if ($data === '') {
            throw new Exception('Decryption failed.');
        }

        $decoded = \base64_decode($data, true);
        if ($decoded === false) {
            throw new Exception('Decryption failed.');
        }

        $decrypted = \openssl_decrypt(
            $decoded,
            'AES-128-CBC',
            $this->hashKey,
            OPENSSL_RAW_DATA,
            $this->hashIV
        );

        if ($decrypted === false) {
            throw new Exception('Decryption failed.');
        }

        return $decrypted;
    }
}

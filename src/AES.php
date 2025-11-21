<?php

declare(strict_types=1);

namespace ecPay\eInvoice;

/**
 * AES encryption and decryption.
 */
trait AES
{
    /**
     * To AES encryption by 128 bit, chiper mode is CBC, padding mode is PKCS7.
     *
     * @param string $data
     * @return string
     */
    protected function encrypt(string $data): string
    {
        $code = \openssl_encrypt($data, 'AES-128-CBC', $this->hashKey, OPENSSL_RAW_DATA, $this->hashIV);

        return \base64_encode($code);
    }

    /**
     * Decrypt the content.
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        $data = base64_decode($data);
        $data = openssl_decrypt($data, 'AES-128-CBC', $this->hashKey, OPENSSL_RAW_DATA, $this->hashIV);

        return \urldecode($data);
    }
}

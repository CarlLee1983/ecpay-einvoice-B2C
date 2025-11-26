<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Tests\Unit;

use CarlLee\EcPayB2C\Exceptions\ConfigurationException;
use CarlLee\EcPayB2C\Exceptions\EncryptionException;
use CarlLee\EcPayB2C\Infrastructure\CipherService;

/**
 * 測試 CipherService 加解密功能
 */
class CipherServiceTest extends UnitTestCase
{
    private CipherService $cipherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cipherService = new CipherService($this->hashKey, $this->hashIV);
    }

    /**
     * 測試加密方法
     */
    public function testEncrypt(): void
    {
        $plaintext = 'Hello World!';

        $encrypted = $this->cipherService->encrypt($plaintext);

        $this->assertIsString($encrypted);
        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($plaintext, $encrypted);

        // Base64 編碼的字串應該只包含特定字元
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/=]+$/', $encrypted);
    }

    /**
     * 測試解密方法
     */
    public function testDecrypt(): void
    {
        $plaintext = 'Test Data 測試資料';

        // 先加密
        $urlEncoded = urlencode($plaintext);
        $encrypted = $this->cipherService->encrypt($urlEncoded);

        // 再解密
        $decrypted = $this->cipherService->decrypt($encrypted);

        $this->assertEquals($urlEncoded, $decrypted);
        $this->assertEquals($plaintext, urldecode($decrypted));
    }

    /**
     * 測試加密解密循環
     */
    public function testEncryptDecryptCycle(): void
    {
        $testData = [
            'Simple text',
            '包含中文的文字',
            'Special chars: !@#$%^&*()',
            '{"json": "data", "number": 123}',
            '   spaces   ',
            'Line1\nLine2\nLine3',
        ];

        foreach ($testData as $plaintext) {
            $urlEncoded = urlencode($plaintext);
            $encrypted = $this->cipherService->encrypt($urlEncoded);
            $decrypted = $this->cipherService->decrypt($encrypted);

            $this->assertEquals($plaintext, urldecode($decrypted), "Failed for: {$plaintext}");
        }
    }

    /**
     * 測試空字串加密解密
     */
    public function testEncryptDecryptEmptyString(): void
    {
        $plaintext = '';

        $encrypted = $this->cipherService->encrypt($plaintext);
        $decrypted = $this->cipherService->decrypt($encrypted);

        $this->assertEquals($plaintext, $decrypted);
    }

    /**
     * 測試長字串加密解密
     */
    public function testEncryptDecryptLongString(): void
    {
        $plaintext = str_repeat('這是一個測試字串。', 100);

        $urlEncoded = urlencode($plaintext);
        $encrypted = $this->cipherService->encrypt($urlEncoded);
        $decrypted = $this->cipherService->decrypt($encrypted);

        $this->assertEquals($plaintext, urldecode($decrypted));
    }

    /**
     * 測試 JSON 資料加密解密
     */
    public function testEncryptDecryptJsonData(): void
    {
        $data = [
            'MerchantID' => 'TEST_MERCHANT',
            'InvoiceNo' => 'AB12345678',
            'Amount' => 1000,
            'Items' => [
                ['name' => '商品A', 'price' => 500],
                ['name' => '商品B', 'price' => 500],
            ],
        ];

        $jsonString = json_encode($data);

        $urlEncoded = urlencode($jsonString);
        $encrypted = $this->cipherService->encrypt($urlEncoded);
        $decrypted = $this->cipherService->decrypt($encrypted);

        $this->assertEquals($jsonString, urldecode($decrypted));

        // 確認解密後可以正確解析 JSON
        $decodedData = json_decode(urldecode($decrypted), true);
        $this->assertEquals($data, $decodedData);
    }

    /**
     * 測試解密無效的 base64 字串
     */
    public function testDecryptInvalidBase64(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('Decryption failed.');

        $this->cipherService->decrypt('invalid base64 string!!!');
    }

    /**
     * 測試解密空字串
     */
    public function testDecryptEmptyString(): void
    {
        $this->expectException(EncryptionException::class);

        $this->cipherService->decrypt('');
    }

    /**
     * 測試使用錯誤的金鑰解密
     */
    public function testDecryptWithWrongKey(): void
    {
        $plaintext = 'Test Data';

        // 使用正確的金鑰加密
        $urlEncoded = urlencode($plaintext);
        $encrypted = $this->cipherService->encrypt($urlEncoded);

        // 創建一個使用錯誤金鑰的實例
        $wrongKeyService = new CipherService(
            'wrongkey12345678',
            'wrongiv123456789'
        );

        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('Decryption failed.');

        $wrongKeyService->decrypt($encrypted);
    }

    /**
     * 測試加密結果的一致性
     */
    public function testEncryptConsistency(): void
    {
        $plaintext = 'Test consistency';

        $encrypted1 = $this->cipherService->encrypt($plaintext);
        $encrypted2 = $this->cipherService->encrypt($plaintext);

        // AES-CBC 模式使用固定的 IV，所以相同的明文應該產生相同的密文
        $this->assertEquals($encrypted1, $encrypted2);
    }

    /**
     * 測試特殊字元處理
     */
    public function testEncryptDecryptSpecialCharacters(): void
    {
        $specialChars = [
            '單引號\'測試',
            '雙引號"測試',
            '反斜線\\測試',
            'URL 字元 &=?#',
            'HTML 標籤 <div>test</div>',
            '換行符號\n\r\t',
        ];

        foreach ($specialChars as $plaintext) {
            $urlEncoded = urlencode($plaintext);
            $encrypted = $this->cipherService->encrypt($urlEncoded);
            $decrypted = $this->cipherService->decrypt($encrypted);

            $this->assertEquals($plaintext, urldecode($decrypted), "Failed for special char: {$plaintext}");
        }
    }

    /**
     * 測試 Unicode 字元處理
     */
    public function testEncryptDecryptUnicode(): void
    {
        $unicodeStrings = [
            '中文測試',
            '日本語テスト',
            '한국어 테스트',
            'Emoji 測試 😀🎉',
            '混合 Mixed 混ぜ 합',
        ];

        foreach ($unicodeStrings as $plaintext) {
            $urlEncoded = urlencode($plaintext);
            $encrypted = $this->cipherService->encrypt($urlEncoded);
            $decrypted = $this->cipherService->decrypt($encrypted);

            $this->assertEquals($plaintext, urldecode($decrypted), "Failed for unicode: {$plaintext}");
        }
    }

    /**
     * 測試空 HashKey 拋出例外
     */
    public function testEmptyHashKeyThrows(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('HashKey is empty.');

        new CipherService('', $this->hashIV);
    }

    /**
     * 測試空 HashIV 拋出例外
     */
    public function testEmptyHashIVThrows(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('HashIV is empty.');

        new CipherService($this->hashKey, '');
    }
}


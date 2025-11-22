<?php

use ecPay\eInvoice\Invoice;
use PHPUnit\Framework\TestCase;

/**
 * 測試 AES 加解密功能
 * 
 * 因為 AES 是 trait，我們使用 Invoice 類別來測試
 */
class AESTest extends TestCase
{
    private Invoice $invoice;
    private string $hashKey = 'ejCk326UnaZWKisg';
    private string $hashIV = 'q9jcZX8Ib9LM8wYk';

    protected function setUp(): void
    {
        $this->invoice = new Invoice(
            'TEST_MERCHANT_ID',
            $this->hashKey,
            $this->hashIV
        );
    }

    /**
     * 測試加密方法
     */
    public function testEncrypt()
    {
        $plaintext = 'Hello World!';
        
        $reflection = new ReflectionClass($this->invoice);
        $method = $reflection->getMethod('encrypt');
        $method->setAccessible(true);
        
        $encrypted = $method->invoke($this->invoice, $plaintext);
        
        $this->assertIsString($encrypted);
        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($plaintext, $encrypted);
        
        // Base64 編碼的字串應該只包含特定字元
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/=]+$/', $encrypted);
    }

    /**
     * 測試解密方法
     */
    public function testDecrypt()
    {
        $plaintext = 'Test Data 測試資料';
        
        // 先加密
        $reflection = new ReflectionClass($this->invoice);
        $encryptMethod = $reflection->getMethod('encrypt');
        $encryptMethod->setAccessible(true);
        
        // URL 編碼（模擬實際使用情境）
        $urlEncoded = urlencode($plaintext);
        $encrypted = $encryptMethod->invoke($this->invoice, $urlEncoded);
        
        // 再解密
        $decrypted = $this->invoice->decrypt($encrypted);
        
        $this->assertEquals($plaintext, $decrypted);
    }

    /**
     * 測試加密解密循環
     */
    public function testEncryptDecryptCycle()
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
            $reflection = new ReflectionClass($this->invoice);
            $encryptMethod = $reflection->getMethod('encrypt');
            $encryptMethod->setAccessible(true);
            
            $urlEncoded = urlencode($plaintext);
            $encrypted = $encryptMethod->invoke($this->invoice, $urlEncoded);
            $decrypted = $this->invoice->decrypt($encrypted);
            
            $this->assertEquals($plaintext, $decrypted, "Failed for: {$plaintext}");
        }
    }

    /**
     * 測試空字串加密解密
     */
    public function testEncryptDecryptEmptyString()
    {
        $plaintext = '';
        
        $reflection = new ReflectionClass($this->invoice);
        $encryptMethod = $reflection->getMethod('encrypt');
        $encryptMethod->setAccessible(true);
        
        $encrypted = $encryptMethod->invoke($this->invoice, $plaintext);
        $decrypted = $this->invoice->decrypt($encrypted);
        
        $this->assertEquals($plaintext, $decrypted);
    }

    /**
     * 測試長字串加密解密
     */
    public function testEncryptDecryptLongString()
    {
        $plaintext = str_repeat('這是一個測試字串。', 100);
        
        $reflection = new ReflectionClass($this->invoice);
        $encryptMethod = $reflection->getMethod('encrypt');
        $encryptMethod->setAccessible(true);
        
        $urlEncoded = urlencode($plaintext);
        $encrypted = $encryptMethod->invoke($this->invoice, $urlEncoded);
        $decrypted = $this->invoice->decrypt($encrypted);
        
        $this->assertEquals($plaintext, $decrypted);
    }

    /**
     * 測試 JSON 資料加密解密
     */
    public function testEncryptDecryptJsonData()
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
        
        $reflection = new ReflectionClass($this->invoice);
        $encryptMethod = $reflection->getMethod('encrypt');
        $encryptMethod->setAccessible(true);
        
        $urlEncoded = urlencode($jsonString);
        $encrypted = $encryptMethod->invoke($this->invoice, $urlEncoded);
        $decrypted = $this->invoice->decrypt($encrypted);
        
        $this->assertEquals($jsonString, $decrypted);
        
        // 確認解密後可以正確解析 JSON
        $decodedData = json_decode($decrypted, true);
        $this->assertEquals($data, $decodedData);
    }

    /**
     * 測試解密無效的 base64 字串
     */
    public function testDecryptInvalidBase64()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Decryption failed.');
        
        $this->invoice->decrypt('invalid base64 string!!!');
    }

    /**
     * 測試解密空字串
     */
    public function testDecryptEmptyString()
    {
        $this->expectException(Exception::class);
        
        $this->invoice->decrypt('');
    }

    /**
     * 測試使用錯誤的金鑰解密
     */
    public function testDecryptWithWrongKey()
    {
        $plaintext = 'Test Data';
        
        // 使用正確的金鑰加密
        $reflection = new ReflectionClass($this->invoice);
        $encryptMethod = $reflection->getMethod('encrypt');
        $encryptMethod->setAccessible(true);
        
        $urlEncoded = urlencode($plaintext);
        $encrypted = $encryptMethod->invoke($this->invoice, $urlEncoded);
        
        // 創建一個使用錯誤金鑰的實例
        $wrongKeyInvoice = new Invoice(
            'TEST_MERCHANT_ID',
            'wrongkey12345678',
            'wrongiv123456789'
        );
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Decryption failed.');
        
        $wrongKeyInvoice->decrypt($encrypted);
    }

    /**
     * 測試加密結果的一致性
     */
    public function testEncryptConsistency()
    {
        $plaintext = 'Test consistency';
        
        $reflection = new ReflectionClass($this->invoice);
        $encryptMethod = $reflection->getMethod('encrypt');
        $encryptMethod->setAccessible(true);
        
        $encrypted1 = $encryptMethod->invoke($this->invoice, $plaintext);
        $encrypted2 = $encryptMethod->invoke($this->invoice, $plaintext);
        
        // AES-CBC 模式使用固定的 IV，所以相同的明文應該產生相同的密文
        $this->assertEquals($encrypted1, $encrypted2);
    }

    /**
     * 測試特殊字元處理
     */
    public function testEncryptDecryptSpecialCharacters()
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
            $reflection = new ReflectionClass($this->invoice);
            $encryptMethod = $reflection->getMethod('encrypt');
            $encryptMethod->setAccessible(true);
            
            $urlEncoded = urlencode($plaintext);
            $encrypted = $encryptMethod->invoke($this->invoice, $urlEncoded);
            $decrypted = $this->invoice->decrypt($encrypted);
            
            $this->assertEquals($plaintext, $decrypted, "Failed for special char: {$plaintext}");
        }
    }

    /**
     * 測試 Unicode 字元處理
     */
    public function testEncryptDecryptUnicode()
    {
        $unicodeStrings = [
            '中文測試',
            '日本語テスト',
            '한국어 테스트',
            'Emoji 測試 😀🎉',
            '混合 Mixed 混ぜ 합',
        ];

        foreach ($unicodeStrings as $plaintext) {
            $reflection = new ReflectionClass($this->invoice);
            $encryptMethod = $reflection->getMethod('encrypt');
            $encryptMethod->setAccessible(true);
            
            $urlEncoded = urlencode($plaintext);
            $encrypted = $encryptMethod->invoke($this->invoice, $urlEncoded);
            $decrypted = $this->invoice->decrypt($encrypted);
            
            $this->assertEquals($plaintext, $decrypted, "Failed for unicode: {$plaintext}");
        }
    }
}


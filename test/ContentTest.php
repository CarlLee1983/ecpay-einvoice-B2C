<?php

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Response;
use PHPUnit\Framework\TestCase;

/**
 * 測試用的 Content 實作類別
 */
class TestableContent extends Content
{
    protected string $requestPath = '/test/path';

    protected function initContent(): void
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'TestField' => '',
        ];
    }

    protected function validation(): void
    {
        $this->validatorBaseParam();
    }

    // 暴露 protected 方法供測試使用
    public function publicGetRqID(): string
    {
        return $this->getRqID();
    }

    public function publicTransUrlencode(string $param): string
    {
        return $this->transUrlencode($param);
    }
}

/**
 * Content 基礎類別測試
 */
class ContentTest extends TestCase
{
    private TestableContent $content;
    private string $merchantId = 'TEST_MERCHANT_ID';
    private string $hashKey = 'ejCk326UnaZWKisg';
    private string $hashIV = 'q9jcZX8Ib9LM8wYk';

    protected function setUp(): void
    {
        $this->content = new TestableContent(
            $this->merchantId,
            $this->hashKey,
            $this->hashIV
        );
    }

    /**
     * 測試建構函數
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(Content::class, $this->content);
        $this->assertInstanceOf(Response::class, $this->content->getResponse());
    }

    /**
     * 測試 getRequestPath
     */
    public function testGetRequestPath()
    {
        $path = $this->content->getRequestPath();

        $this->assertEquals('/test/path', $path);
    }

    /**
     * 測試 setMerchantID
     */
    public function testSetMerchantID()
    {
        $newId = 'NEW_MERCHANT_ID';
        $result = $this->content->setMerchantID($newId);

        $this->assertInstanceOf(TestableContent::class, $result);

        $reflection = new ReflectionClass($this->content);
        $property = $reflection->getProperty('merchantID');
        $property->setAccessible(true);

        $this->assertEquals($newId, $property->getValue($this->content));
    }

    /**
     * 測試 setHashKey
     */
    public function testSetHashKey()
    {
        $newKey = 'newHashKey1234567';
        $result = $this->content->setHashKey($newKey);

        $this->assertInstanceOf(TestableContent::class, $result);

        $reflection = new ReflectionClass($this->content);
        $property = $reflection->getProperty('hashKey');
        $property->setAccessible(true);

        $this->assertEquals($newKey, $property->getValue($this->content));
    }

    /**
     * 測試 setHashIV
     */
    public function testSetHashIV()
    {
        $newIV = 'newHashIV12345678';
        $result = $this->content->setHashIV($newIV);

        $this->assertInstanceOf(TestableContent::class, $result);

        $reflection = new ReflectionClass($this->content);
        $property = $reflection->getProperty('hashIV');
        $property->setAccessible(true);

        $this->assertEquals($newIV, $property->getValue($this->content));
    }

    /**
     * 測試 setRelateNumber - 成功
     */
    public function testSetRelateNumberSuccess()
    {
        $relateNumber = 'TEST202401150001';
        $result = $this->content->setRelateNumber($relateNumber);

        $this->assertInstanceOf(TestableContent::class, $result);
    }

    /**
     * 測試 setRelateNumber - 超過長度限制
     */
    public function testSetRelateNumberTooLong()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('RelateNumber 不可超過 30 個字元');

        $longRelateNumber = str_repeat('A', 31);
        $this->content->setRelateNumber($longRelateNumber);
    }

    /**
     * 測試 setRelateNumber - 邊界值（30字元）
     */
    public function testSetRelateNumberMaxLength()
    {
        $relateNumber = str_repeat('A', 30);
        $result = $this->content->setRelateNumber($relateNumber);

        $this->assertInstanceOf(TestableContent::class, $result);
    }

    /**
     * 測試 setInvoiceDate - 成功
     */
    public function testSetInvoiceDateSuccess()
    {
        $validDates = [
            '2024-01-01',
            '2024-12-31',
            '2023-06-15',
        ];

        foreach ($validDates as $date) {
            $result = $this->content->setInvoiceDate($date);
            $this->assertInstanceOf(TestableContent::class, $result);
        }
    }

    /**
     * 測試 setInvoiceDate - 格式錯誤
     */
    public function testSetInvoiceDateInvalidFormat()
    {
        $invalidDates = [
            '2024/01/01',
            '01-01-2024',
            '2024-1-1',
            '2024-13-01',
            '2024-01-32',
            'invalid date',
        ];

        foreach ($invalidDates as $date) {
            try {
                $this->content->setInvoiceDate($date);
                $this->fail("應該拋出異常，但沒有：{$date}");
            } catch (Exception $e) {
                $this->assertStringContainsString('InvoiceDate', $e->getMessage());
            }
        }
    }

    /**
     * 測試 getRqID 格式
     */
    public function testGetRqID()
    {
        $rqId = $this->content->publicGetRqID();

        $this->assertIsString($rqId);
        $this->assertNotEmpty($rqId);

        // RqID 應該包含時間戳和隨機字串
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]+$/', $rqId);
    }

    /**
     * 測試 getRqID 的唯一性
     */
    public function testGetRqIDUniqueness()
    {
        $rqId1 = $this->content->publicGetRqID();
        usleep(1000); // 等待 1 毫秒
        $rqId2 = $this->content->publicGetRqID();

        $this->assertNotEquals($rqId1, $rqId2);
    }

    /**
     * 測試 transUrlencode
     */
    public function testTransUrlencode()
    {
        $testCases = [
            '%2d' => '-',
            '%5f' => '_',
            '%2e' => '.',
            '%21' => '!',
            '%2a' => '*',
            '%28' => '(',
            '%29' => ')',
        ];

        foreach ($testCases as $input => $expected) {
            $result = $this->content->publicTransUrlencode($input);
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * 測試 transUrlencode - 複雜字串
     */
    public function testTransUrlencodeComplexString()
    {
        $input = 'test%2d%5f%2e%21%2a%28%29data';
        $expected = 'test-_.!*()data';

        $result = $this->content->publicTransUrlencode($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * 測試初始化內容包含必要結構
     */
    public function testInitialContentStructure()
    {
        $reflection = new ReflectionClass($this->content);
        $property = $reflection->getProperty('content');
        $property->setAccessible(true);
        $content = $property->getValue($this->content);

        $this->assertArrayHasKey('MerchantID', $content);
        $this->assertArrayHasKey('RqHeader', $content);
        $this->assertArrayHasKey('Data', $content);

        $this->assertEquals($this->merchantId, $content['MerchantID']);

        // 檢查 RqHeader 結構
        $this->assertArrayHasKey('Timestamp', $content['RqHeader']);
    }

    /**
     * 測試常數定義
     */
    public function testConstants()
    {
        $this->assertEquals(30, Content::RELATE_NUMBER_MAX_LENGTH);
        $this->assertEquals(5, Content::RQID_RANDOM_LENGTH);
    }

    /**
     * 測試 Fluent Interface
     */
    public function testFluentInterface()
    {
        $result = $this->content
            ->setMerchantID('TEST123')
            ->setHashKey('testkey123456789')
            ->setHashIV('testiv1234567890')
            ->setRelateNumber('REL123');

        $this->assertInstanceOf(TestableContent::class, $result);
    }

    /**
     * 測試 validation 方法（基礎驗證）
     */
    public function testValidation()
    {
        $this->expectNotToPerformAssertions();
        $this->content->getPayload();
    }

    /**
     * 測試設定空的 RelateNumber
     */
    public function testSetEmptyRelateNumber()
    {
        $result = $this->content->setRelateNumber('');

        $this->assertInstanceOf(TestableContent::class, $result);
    }

    /**
     * 測試不同長度的 RelateNumber
     */
    public function testVariousRelateNumberLengths()
    {
        $lengths = [1, 5, 10, 15, 20, 25, 29, 30];

        foreach ($lengths as $length) {
            $relateNumber = str_repeat('A', $length);
            $result = $this->content->setRelateNumber($relateNumber);
            $this->assertInstanceOf(TestableContent::class, $result);
        }
    }

    /**
     * 測試 RelateNumber 包含特殊字元
     */
    public function testRelateNumberWithSpecialCharacters()
    {
        $relateNumbers = [
            'TEST-2024-01-15',
            'ORDER_20240115_001',
            'REL.123.456',
            '發票編號_20240115',
        ];

        foreach ($relateNumbers as $relateNumber) {
            if (strlen($relateNumber) <= 30) {
                $result = $this->content->setRelateNumber($relateNumber);
                $this->assertInstanceOf(TestableContent::class, $result);
            }
        }
    }

    /**
     * 測試連續多次設定不同的日期
     */
    public function testMultipleInvoiceDateChanges()
    {
        $dates = ['2024-01-01', '2024-02-15', '2024-12-31'];

        foreach ($dates as $date) {
            $result = $this->content->setInvoiceDate($date);
            $this->assertInstanceOf(TestableContent::class, $result);
        }
    }

    /**
     * 測試 RqHeader Timestamp 為有效時間戳
     */
    public function testRqHeaderTimestamp()
    {
        $reflection = new ReflectionClass($this->content);
        $property = $reflection->getProperty('content');
        $property->setAccessible(true);
        $content = $property->getValue($this->content);

        $timestamp = $content['RqHeader']['Timestamp'];

        $this->assertIsInt($timestamp);
        $this->assertGreaterThan(0, $timestamp);
        $this->assertLessThanOrEqual(time(), $timestamp);
    }
}

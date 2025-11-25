<?php

use CarlLee\EcPayB2C\Queries\GetInvalidInvoice;
use PHPUnit\Framework\TestCase;

class GetInvalidInvoiceTest extends TestCase
{
    private GetInvalidInvoice $getInvalidInvoice;

    protected function setUp(): void
    {
        $this->getInvalidInvoice = new GetInvalidInvoice(
            'TEST_MERCHANT_ID',
            'ejCk326UnaZWKisg',
            'q9jcZX8Ib9LM8wYk'
        );
    }

    /**
     * 輔助方法：讀取 content（不觸發驗證）
     */
    private function getContentWithoutValidation(): array
    {
        $reflection = new ReflectionClass($this->getInvalidInvoice);
        $property = $reflection->getProperty('content');
        $property->setAccessible(true);
        return $property->getValue($this->getInvalidInvoice);
    }

    /**
     * 輔助方法：設定 RelateNumber（使用反射）
     */
    private function setRelateNumber(string $relateNumber): void
    {
        $reflection = new ReflectionClass($this->getInvalidInvoice);
        $property = $reflection->getProperty('content');
        $property->setAccessible(true);
        $content = $property->getValue($this->getInvalidInvoice);
        $content['Data']['RelateNumber'] = $relateNumber;
        $property->setValue($this->getInvalidInvoice, $content);
    }

    /**
     * 輔助方法：設定 InvoiceDate（使用反射）
     */
    private function setInvoiceDate(string $date): void
    {
        $reflection = new ReflectionClass($this->getInvalidInvoice);
        $property = $reflection->getProperty('content');
        $property->setAccessible(true);
        $content = $property->getValue($this->getInvalidInvoice);
        $content['Data']['InvoiceDate'] = $date;
        $property->setValue($this->getInvalidInvoice, $content);
    }

    /**
     * 測試設定發票號碼 - 成功
     */
    public function testSetInvoiceNoSuccess()
    {
        $result = $this->getInvalidInvoice->setInvoiceNo('AB12345678');

        $this->assertInstanceOf(GetInvalidInvoice::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals('AB12345678', $content['Data']['InvoiceNo']);
    }

    /**
     * 測試設定發票號碼 - 長度不正確（太短）
     */
    public function testSetInvoiceNoTooShort()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice no length should be 10.');

        $this->getInvalidInvoice->setInvoiceNo('AB123');
    }

    /**
     * 測試設定發票號碼 - 長度不正確（太長）
     */
    public function testSetInvoiceNoTooLong()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice no length should be 10.');

        $this->getInvalidInvoice->setInvoiceNo('AB1234567890');
    }

    /**
     * 測試驗證 - 缺少發票號碼
     */
    public function testValidationMissingInvoiceNo()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice no is empty.');

        $this->getInvalidInvoice->validation();
    }

    /**
     * 測試驗證 - 成功
     */
    public function testValidationSuccess()
    {
        $this->getInvalidInvoice->setInvoiceNo('AB12345678');

        $this->expectNotToPerformAssertions();
        $this->getInvalidInvoice->validation();
    }

    /**
     * 測試 Fluent Interface
     */
    public function testFluentInterface()
    {
        $result = $this->getInvalidInvoice->setInvoiceNo('AB12345678');

        $this->assertInstanceOf(GetInvalidInvoice::class, $result);

        $content = $this->getContentWithoutValidation();
        $this->assertEquals('AB12345678', $content['Data']['InvoiceNo']);
    }

    /**
     * 測試完整流程 - getContent
     */
    public function testGetContentWithCompleteData()
    {
        $this->getInvalidInvoice->setInvoiceNo('AB12345678');

        $content = $this->getInvalidInvoice->getContent();

        $this->assertIsArray($content);
        $this->assertArrayHasKey('Data', $content);
        $this->assertIsString($content['Data']);
    }

    /**
     * 測試初始化內容包含必要欄位
     */
    public function testInitContentHasRequiredFields()
    {
        $content = $this->getContentWithoutValidation();

        $this->assertArrayHasKey('Data', $content);
        $this->assertArrayHasKey('MerchantID', $content['Data']);
        $this->assertArrayHasKey('RelateNumber', $content['Data']);
        $this->assertArrayHasKey('InvoiceNo', $content['Data']);
        $this->assertArrayHasKey('InvoiceDate', $content['Data']);

        $this->assertEquals('TEST_MERCHANT_ID', $content['Data']['MerchantID']);
    }

    /**
     * 測試設定完整的查詢資料
     */
    public function testSetCompleteQueryData()
    {
        $this->setRelateNumber('TEST202401150001');
        $this->setInvoiceDate('2024-01-15');
        $this->getInvalidInvoice->setInvoiceNo('AB12345678');

        $content = $this->getContentWithoutValidation();

        $this->assertEquals('TEST202401150001', $content['Data']['RelateNumber']);
        $this->assertEquals('2024-01-15', $content['Data']['InvoiceDate']);
        $this->assertEquals('AB12345678', $content['Data']['InvoiceNo']);
    }

    /**
     * 測試多個不同的發票號碼格式
     */
    public function testVariousInvoiceNumbers()
    {
        $invoiceNumbers = [
            'AB12345678',
            'CD98765432',
            'EF00000001',
            'ZZ99999999',
        ];

        foreach ($invoiceNumbers as $invoiceNo) {
            $this->getInvalidInvoice->setInvoiceNo($invoiceNo);
            $content = $this->getContentWithoutValidation();
            $this->assertEquals($invoiceNo, $content['Data']['InvoiceNo']);
        }
    }

    /**
     * 測試發票號碼只能是 10 個字元
     */
    public function testInvoiceNoMustBeTenCharacters()
    {
        $invalidNumbers = [
            '',          // 0 個字元
            'A',         // 1 個字元
            'AB1234567', // 9 個字元
            'AB123456789', // 11 個字元
            'ABCDEFGHIJK', // 11 個字元
        ];

        foreach ($invalidNumbers as $invalidNumber) {
            try {
                $this->getInvalidInvoice->setInvoiceNo($invalidNumber);
                $this->fail("應該拋出異常，但沒有：{$invalidNumber}");
            } catch (Exception $e) {
                $this->assertEquals('The invoice no length should be 10.', $e->getMessage());
            }
        }
    }
}

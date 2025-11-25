<?php

use CarlLee\EcPayB2C\Operations\AllowanceInvalid;
use PHPUnit\Framework\TestCase;

class AllowanceInvalidTest extends TestCase
{
    private AllowanceInvalid $allowanceInvalid;

    protected function setUp(): void
    {
        $this->allowanceInvalid = new AllowanceInvalid(
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
        $reflection = new ReflectionClass($this->allowanceInvalid);
        $property = $reflection->getProperty('content');
        $property->setAccessible(true);
        return $property->getValue($this->allowanceInvalid);
    }

    /**
     * 測試設定發票號碼 - 成功
     */
    public function testSetInvoiceNoSuccess()
    {
        $result = $this->allowanceInvalid->setInvoiceNo('AB12345678');

        $this->assertInstanceOf(AllowanceInvalid::class, $result);
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

        $this->allowanceInvalid->setInvoiceNo('AB123');
    }

    /**
     * 測試設定發票號碼 - 長度不正確（太長）
     */
    public function testSetInvoiceNoTooLong()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice no length should be 10.');

        $this->allowanceInvalid->setInvoiceNo('AB1234567890');
    }

    /**
     * 測試設定折讓號碼 - 成功
     */
    public function testSetAllowanceNoSuccess()
    {
        $result = $this->allowanceInvalid->setAllowanceNo('1234567890123456');

        $this->assertInstanceOf(AllowanceInvalid::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals('1234567890123456', $content['Data']['AllowanceNo']);
    }

    /**
     * 測試設定折讓號碼 - 長度不正確（太短）
     */
    public function testSetAllowanceNoTooShort()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice allowance no length should be 16.');

        $this->allowanceInvalid->setAllowanceNo('123456');
    }

    /**
     * 測試設定折讓號碼 - 長度不正確（太長）
     */
    public function testSetAllowanceNoTooLong()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice allowance no length should be 16.');

        $this->allowanceInvalid->setAllowanceNo('12345678901234567890');
    }

    /**
     * 測試設定作廢原因 - 成功
     */
    public function testSetReasonSuccess()
    {
        $result = $this->allowanceInvalid->setReason('客戶退貨');

        $this->assertInstanceOf(AllowanceInvalid::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals('客戶退貨', $content['Data']['Reason']);
    }

    /**
     * 測試設定作廢原因 - 空字串也能設定
     */
    public function testSetReasonEmpty()
    {
        $result = $this->allowanceInvalid->setReason('');

        $this->assertInstanceOf(AllowanceInvalid::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals('', $content['Data']['Reason']);
    }

    /**
     * 測試驗證 - 缺少發票號碼
     */
    public function testValidationMissingInvoiceNo()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice no is empty.');

        $this->allowanceInvalid
            ->setAllowanceNo('1234567890123456')
            ->setReason('退貨');

        $this->allowanceInvalid->validation();
    }

    /**
     * 測試驗證 - 缺少折讓號碼
     */
    public function testValidationMissingAllowanceNo()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice allowance no is empty.');

        $this->allowanceInvalid
            ->setInvoiceNo('AB12345678')
            ->setReason('退貨');

        $this->allowanceInvalid->validation();
    }

    /**
     * 測試驗證 - 缺少作廢原因
     */
    public function testValidationMissingReason()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice invalid reason is empty.');

        $this->allowanceInvalid
            ->setInvoiceNo('AB12345678')
            ->setAllowanceNo('1234567890123456');

        $this->allowanceInvalid->validation();
    }

    /**
     * 測試驗證 - 成功
     */
    public function testValidationSuccess()
    {
        $this->allowanceInvalid
            ->setInvoiceNo('AB12345678')
            ->setAllowanceNo('1234567890123456')
            ->setReason('客戶要求退貨，商品有瑕疵');

        $this->expectNotToPerformAssertions();
        $this->allowanceInvalid->validation();
    }

    /**
     * 測試 Fluent Interface - 鏈式呼叫
     */
    public function testFluentInterface()
    {
        $result = $this->allowanceInvalid
            ->setInvoiceNo('AB12345678')
            ->setAllowanceNo('1234567890123456')
            ->setReason('測試作廢原因');

        $this->assertInstanceOf(AllowanceInvalid::class, $result);

        $content = $this->getContentWithoutValidation();
        $this->assertEquals('AB12345678', $content['Data']['InvoiceNo']);
        $this->assertEquals('1234567890123456', $content['Data']['AllowanceNo']);
        $this->assertEquals('測試作廢原因', $content['Data']['Reason']);
    }

    /**
     * 測試完整流程 - getContent
     */
    public function testGetContentWithCompleteData()
    {
        $this->allowanceInvalid
            ->setInvoiceNo('AB12345678')
            ->setAllowanceNo('1234567890123456')
            ->setReason('折讓單據錯誤，需要重新開立');

        $content = $this->allowanceInvalid->getContent();

        $this->assertIsArray($content);
        $this->assertArrayHasKey('Data', $content);
        $this->assertIsString($content['Data']);
    }

    /**
     * 測試各種作廢原因
     */
    public function testVariousReasons()
    {
        $reasons = [
            '客戶退貨',
            '商品瑕疵',
            '價格錯誤',
            '數量錯誤',
            '客戶要求取消訂單',
            'System error - need to re-issue',
        ];

        foreach ($reasons as $reason) {
            $this->allowanceInvalid->setReason($reason);
            $content = $this->getContentWithoutValidation();
            $this->assertEquals($reason, $content['Data']['Reason']);
        }
    }
}

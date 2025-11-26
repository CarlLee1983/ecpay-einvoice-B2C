<?php

use CarlLee\EcPayB2C\Notifications\InvoiceNotify;
use CarlLee\EcPayB2C\Parameter\InvoiceTagType;
use CarlLee\EcPayB2C\Parameter\NotifiedType;
use CarlLee\EcPayB2C\Parameter\NotifyType;
use PHPUnit\Framework\TestCase;

class InvoiceNotifyTest extends TestCase
{
    private InvoiceNotify $invoiceNotify;

    protected function setUp(): void
    {
        $this->invoiceNotify = new InvoiceNotify(
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
        $reflection = new ReflectionClass($this->invoiceNotify);
        $property = $reflection->getProperty('content');
        $property->setAccessible(true);
        return $property->getValue($this->invoiceNotify);
    }

    /**
     * 測試設定發票號碼 - 成功
     */
    public function testSetInvoiceNoSuccess()
    {
        $result = $this->invoiceNotify->setInvoiceNo('AB12345678');

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals('AB12345678', $content['Data']['InvoiceNo']);
    }

    /**
     * 測試設定發票號碼 - 長度錯誤
     */
    public function testSetInvoiceNoInvalidLength()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice no length should be 10.');

        $this->invoiceNotify->setInvoiceNo('AB123');
    }

    /**
     * 測試設定折讓號碼 - 成功
     */
    public function testSetAllowanceNoSuccess()
    {
        $result = $this->invoiceNotify->setAllowanceNo('1234567890123456');

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals('1234567890123456', $content['Data']['AllowanceNo']);
    }

    /**
     * 測試設定折讓號碼 - 長度錯誤
     */
    public function testSetAllowanceNoInvalidLength()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice allowance no length should be 16.');

        $this->invoiceNotify->setAllowanceNo('123456');
    }

    /**
     * 測試設定電話號碼 - 成功
     */
    public function testSetPhoneSuccess()
    {
        $result = $this->invoiceNotify->setPhone('0912345678');

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals('0912345678', $content['Data']['Phone']);
    }

    /**
     * 測試設定電話號碼 - 超過長度限制
     */
    public function testSetPhoneTooLong()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Notify phone number should be less than 21 characters');

        $this->invoiceNotify->setPhone(str_repeat('1', 21));
    }

    /**
     * 測試設定電話號碼 - 邊界值測試（20字元）
     */
    public function testSetPhoneMaxLength()
    {
        $phone = str_repeat('1', 20);
        $result = $this->invoiceNotify->setPhone($phone);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals($phone, $content['Data']['Phone']);
    }

    /**
     * 測試設定 Email - 成功
     */
    public function testSetNotifyMailSuccess()
    {
        $result = $this->invoiceNotify->setNotifyMail('test@example.com');

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals('test@example.com', $content['Data']['NotifyMail']);
    }

    /**
     * 測試設定 Email - 格式錯誤
     */
    public function testSetNotifyMailInvalidFormat()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid email format');

        $this->invoiceNotify->setNotifyMail('invalid-email');
    }

    /**
     * 測試設定 Email - 超過長度限制
     */
    public function testSetNotifyMailTooLong()
    {
        $this->expectException(Exception::class);

        $longEmail = str_repeat('test', 25) . '@example.com'; // 超過 80 字元
        $this->invoiceNotify->setNotifyMail($longEmail);
    }

    /**
     * 測試設定通知類型 - SMS
     */
    public function testSetNotifyTypeSms()
    {
        $result = $this->invoiceNotify->setNotify(NotifyType::SMS->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(NotifyType::SMS->value, $content['Data']['Notify']);
    }

    /**
     * 測試設定通知類型 - Email
     */
    public function testSetNotifyTypeEmail()
    {
        $result = $this->invoiceNotify->setNotify(NotifyType::EMAIL->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(NotifyType::EMAIL->value, $content['Data']['Notify']);
    }

    /**
     * 測試設定通知類型 - All
     */
    public function testSetNotifyTypeAll()
    {
        $result = $this->invoiceNotify->setNotify(NotifyType::ALL->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(NotifyType::ALL->value, $content['Data']['Notify']);
    }

    /**
     * 測試設定通知類型 - 無效類型
     */
    public function testSetNotifyInvalidType()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Notify type format is invalid.');

        $this->invoiceNotify->setNotify('X');
    }

    /**
     * 測試設定發票標籤 - 發票開立
     */
    public function testSetInvoiceTagInvoice()
    {
        $result = $this->invoiceNotify->setInvoiceTag(InvoiceTagType::INVOICE->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(InvoiceTagType::INVOICE->value, $content['Data']['InvoiceTag']);
    }

    /**
     * 測試設定發票標籤 - 發票作廢
     */
    public function testSetInvoiceTagInvoiceVoid()
    {
        $result = $this->invoiceNotify->setInvoiceTag(InvoiceTagType::INVOICE_VOID->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(InvoiceTagType::INVOICE_VOID->value, $content['Data']['InvoiceTag']);
    }

    /**
     * 測試設定發票標籤 - 折讓開立
     */
    public function testSetInvoiceTagAllowance()
    {
        $result = $this->invoiceNotify->setInvoiceTag(InvoiceTagType::ALLOWANCE->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(InvoiceTagType::ALLOWANCE->value, $content['Data']['InvoiceTag']);
    }

    /**
     * 測試設定發票標籤 - 折讓作廢
     */
    public function testSetInvoiceTagAllowanceVoid()
    {
        $result = $this->invoiceNotify->setInvoiceTag(InvoiceTagType::ALLOWANCE_VOID->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(InvoiceTagType::ALLOWANCE_VOID->value, $content['Data']['InvoiceTag']);
    }

    /**
     * 測試設定發票標籤 - 發票中獎
     */
    public function testSetInvoiceTagInvoiceWinning()
    {
        $result = $this->invoiceNotify->setInvoiceTag(InvoiceTagType::INVOICE_WINNING->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(InvoiceTagType::INVOICE_WINNING->value, $content['Data']['InvoiceTag']);
    }

    /**
     * 測試設定發票標籤 - 無效標籤
     */
    public function testSetInvoiceTagInvalid()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice notify tag is invalid.');

        $this->invoiceNotify->setInvoiceTag('INVALID');
    }

    /**
     * 測試設定通知對象 - 客戶
     */
    public function testSetNotifiedCustomer()
    {
        $result = $this->invoiceNotify->setNotified(NotifiedType::CUSTOMER->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(NotifiedType::CUSTOMER->value, $content['Data']['Notified']);
    }

    /**
     * 測試設定通知對象 - 廠商
     */
    public function testSetNotifiedVendor()
    {
        $result = $this->invoiceNotify->setNotified(NotifiedType::VENDOR->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(NotifiedType::VENDOR->value, $content['Data']['Notified']);
    }

    /**
     * 測試設定通知對象 - 全部
     */
    public function testSetNotifiedAll()
    {
        $result = $this->invoiceNotify->setNotified(NotifiedType::ALL->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);
        $content = $this->getContentWithoutValidation();
        $this->assertEquals(NotifiedType::ALL->value, $content['Data']['Notified']);
    }

    /**
     * 測試設定通知對象 - 無效對象
     */
    public function testSetNotifiedInvalid()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Notify target is invalid.');

        $this->invoiceNotify->setNotified('X');
    }

    /**
     * 測試驗證 - 缺少發票號碼
     */
    public function testValidationMissingInvoiceNo()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice no is empty.');

        $this->invoiceNotify
            ->setPhone('0912345678')
            ->setNotify(NotifyType::SMS->value)
            ->setInvoiceTag(InvoiceTagType::INVOICE->value)
            ->setNotified(NotifiedType::CUSTOMER->value);

        $this->invoiceNotify->getPayload();
    }

    /**
     * 測試驗證 - 折讓類型需要折讓號碼
     */
    public function testValidationAllowanceTagNeedsAllowanceNo()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invoice tag type is allowed or allowed invalid, `AllowanceNo` should be set.');

        $this->invoiceNotify
            ->setInvoiceNo('AB12345678')
            ->setPhone('0912345678')
            ->setNotify(NotifyType::SMS->value)
            ->setInvoiceTag(InvoiceTagType::ALLOWANCE->value)
            ->setNotified(NotifiedType::CUSTOMER->value);

        $this->invoiceNotify->getPayload();
    }

    /**
     * 測試驗證 - 缺少聯絡方式
     */
    public function testValidationMissingContact()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Phone number or mail should be set.');

        $this->invoiceNotify
            ->setInvoiceNo('AB12345678')
            ->setNotify(NotifyType::SMS->value)
            ->setInvoiceTag(InvoiceTagType::INVOICE->value)
            ->setNotified(NotifiedType::CUSTOMER->value);

        $this->invoiceNotify->getPayload();
    }

    /**
     * 測試驗證 - 缺少通知類型
     */
    public function testValidationMissingNotify()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Notify is empty.');

        $this->invoiceNotify
            ->setInvoiceNo('AB12345678')
            ->setPhone('0912345678')
            ->setInvoiceTag(InvoiceTagType::INVOICE->value)
            ->setNotified(NotifiedType::CUSTOMER->value);

        $this->invoiceNotify->getPayload();
    }

    /**
     * 測試驗證 - 缺少發票標籤
     */
    public function testValidationMissingInvoiceTag()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invoice tag is empty.');

        $this->invoiceNotify
            ->setInvoiceNo('AB12345678')
            ->setPhone('0912345678')
            ->setNotify(NotifyType::SMS->value)
            ->setNotified(NotifiedType::CUSTOMER->value);

        $this->invoiceNotify->getPayload();
    }

    /**
     * 測試驗證 - 缺少通知對象
     */
    public function testValidationMissingNotified()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Notified is empty.');

        $this->invoiceNotify
            ->setInvoiceNo('AB12345678')
            ->setPhone('0912345678')
            ->setNotify(NotifyType::SMS->value)
            ->setInvoiceTag(InvoiceTagType::INVOICE->value);

        $this->invoiceNotify->getPayload();
    }

    /**
     * 測試驗證 - 成功（發票通知）
     */
    public function testValidationSuccessInvoice()
    {
        $this->invoiceNotify
            ->setInvoiceNo('AB12345678')
            ->setPhone('0912345678')
            ->setNotify(NotifyType::SMS->value)
            ->setInvoiceTag(InvoiceTagType::INVOICE->value)
            ->setNotified(NotifiedType::CUSTOMER->value);

        $this->expectNotToPerformAssertions();
        $this->invoiceNotify->getPayload();
    }

    /**
     * 測試驗證 - 成功（折讓通知）
     */
    public function testValidationSuccessAllowance()
    {
        $this->invoiceNotify
            ->setInvoiceNo('AB12345678')
            ->setAllowanceNo('1234567890123456')
            ->setNotifyMail('test@example.com')
            ->setNotify(NotifyType::EMAIL->value)
            ->setInvoiceTag(InvoiceTagType::ALLOWANCE->value)
            ->setNotified(NotifiedType::CUSTOMER->value);

        $this->expectNotToPerformAssertions();
        $this->invoiceNotify->getPayload();
    }

    /**
     * 測試 Fluent Interface - 完整鏈式呼叫
     */
    public function testFluentInterface()
    {
        $result = $this->invoiceNotify
            ->setInvoiceNo('AB12345678')
            ->setPhone('0912345678')
            ->setNotifyMail('test@example.com')
            ->setNotify(NotifyType::ALL->value)
            ->setInvoiceTag(InvoiceTagType::INVOICE->value)
            ->setNotified(NotifiedType::CUSTOMER->value);

        $this->assertInstanceOf(InvoiceNotify::class, $result);

        $content = $this->getContentWithoutValidation();
        $this->assertEquals('AB12345678', $content['Data']['InvoiceNo']);
        $this->assertEquals('0912345678', $content['Data']['Phone']);
        $this->assertEquals('test@example.com', $content['Data']['NotifyMail']);
        $this->assertEquals(NotifyType::ALL->value, $content['Data']['Notify']);
        $this->assertEquals(InvoiceTagType::INVOICE->value, $content['Data']['InvoiceTag']);
        $this->assertEquals(NotifiedType::CUSTOMER->value, $content['Data']['Notified']);
    }

    /**
     * 測試完整流程 - getContent
     */
    public function testGetContentWithCompleteData()
    {
        $this->invoiceNotify
            ->setInvoiceNo('AB12345678')
            ->setPhone('0912345678')
            ->setNotify(NotifyType::SMS->value)
            ->setInvoiceTag(InvoiceTagType::INVOICE->value)
            ->setNotified(NotifiedType::CUSTOMER->value);

        $content = $this->invoiceNotify->getContent();

        $this->assertIsArray($content);
        $this->assertArrayHasKey('Data', $content);
        $this->assertIsString($content['Data']);
    }
}

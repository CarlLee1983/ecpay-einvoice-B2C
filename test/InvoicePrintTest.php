<?php

class InvoicePrintTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CarlLee\EcPayB2C\Operations\InvoicePrint
     */
    private $instance;

    protected function setUp(): void
    {
        $this->instance = new CarlLee\EcPayB2C\Operations\InvoicePrint(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
    }

    public function testInvoiceNoFormat()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceNo 格式錯誤，需為 2 碼英文 + 8 碼數字。');

        $this->instance->setInvoiceNo('1234567890');
    }

    public function testInvoiceDateFormat()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceDate 格式需為 yyyy-MM-dd 或 yyyy/MM/dd。');

        $this->instance->setInvoiceDate('20240101');
    }

    public function testInvalidPrintStyle()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('PrintStyle 僅支援 1~5。');

        $this->instance->setPrintStyle(7);
    }

    public function testValidationRequiresDate()
    {
        $this->instance->setInvoiceNo('UV11100016');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceDate 不可為空。');

        $this->instance->getContent();
    }

    public function testRequestPath()
    {
        $content = $this->instance
            ->setInvoiceNo('UV11100016')
            ->setInvoiceDate('2024-01-10')
            ->setPrintStyle(2)
            ->setReprint(true)
            ->setShowingDetail(1)
            ->getContent();

        $this->assertEquals('/B2CInvoice/InvoicePrint', $this->instance->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }
}

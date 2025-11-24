<?php

class GetAllowanceListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ecPay\eInvoice\Queries\GetAllowanceList
     */
    private $instance;

    protected function setUp(): void
    {
        $this->instance = new ecPay\eInvoice\Queries\GetAllowanceList(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
    }

    public function testAllowanceNoLength()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('AllowanceNo 長度需為 16 碼。');

        $this->instance->setAllowanceNo('123');
    }

    public function testInvoiceNoFormat()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceNo 格式錯誤，需為 2 碼英文 + 8 碼數字。');

        $this->instance->setInvoiceNo('1234567890');
    }

    public function testDateFormat()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Date 格式需為 yyyy-MM-dd 或 yyyy/MM/dd。');

        $this->instance->setDate('20240135');
    }

    public function testValidationType0()
    {
        $this->instance->setSearchType('0');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SearchType 為 0 時，AllowanceNo 為必填。');

        $this->instance->getContent();
    }

    public function testValidationType1()
    {
        $this->instance->setSearchType('1')
            ->setInvoiceNo('AB12345678');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SearchType 為 1 或 2 時，Date 為必填。');

        $this->instance->getContent();
    }

    public function testRequestPath()
    {
        $content = $this->instance->setSearchType('0')
            ->setAllowanceNo('2019091719477262')
            ->getContent();

        $this->assertEquals('/B2CInvoice/GetAllowanceList', $this->instance->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }
}

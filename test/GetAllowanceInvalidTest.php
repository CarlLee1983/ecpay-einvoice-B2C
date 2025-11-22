<?php

class GetAllowanceInvalidTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ecPay\eInvoice\Queries\GetAllowanceInvalid
     */
    private $instance;

    protected function setUp(): void
    {
        $this->instance = new ecPay\eInvoice\Queries\GetAllowanceInvalid(
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

    public function testAllowanceNoLength()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('AllowanceNo 長度需為 16 碼。');

        $this->instance->setAllowanceNo('123');
    }

    public function testValidationRequiresFields()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceNo 不可為空。');

        $this->instance->getContent();
    }

    public function testRequestPath()
    {
        $content = $this->instance->setInvoiceNo('UV11100016')
            ->setAllowanceNo('2019091719477262')
            ->getContent();

        $this->assertEquals('/B2CInvoice/GetAllowanceInvalid', $this->instance->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }
}


<?php

declare(strict_types=1);

use ecPay\eInvoice\Parameter\InvType;
use ecPay\eInvoice\Queries\GetInvoiceWordSetting;
use PHPUnit\Framework\TestCase;

class GetInvoiceWordSettingTest extends TestCase
{
    private GetInvoiceWordSetting $query;

    protected function setUp(): void
    {
        $this->query = new GetInvoiceWordSetting(
            '2000132',
            'ejCk326UnaZWKisg',
            'q9jcZX8Ib9LM8wYk'
        );
    }

    public function testInvoiceYearRequired(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceYear cannot be empty.');

        $this->query->getContent();
    }

    public function testInvoiceYearConversion(): void
    {
        $year = (int) date('Y') + 1;
        $expected = str_pad((string) ($year - 1911), 3, '0', STR_PAD_LEFT);

        $this->query->setInvoiceYear($year);
        $content = $this->getRawContent();

        $this->assertSame($expected, $content['Data']['InvoiceYear']);
    }

    public function testInvoiceTermOutOfRange(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceTerm must be between 0 and 6.');

        $this->query->setInvoiceTerm(7);
    }

    public function testUseStatusOutOfRange(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('UseStatus must be between 0 and 6.');

        $this->query->setUseStatus(-1);
    }

    public function testInvTypeValidation(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvType only supports 07 or 08.');

        $this->query->setInvType('09');
    }

    public function testProductServiceIdValidation(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ProductServiceId must be 1-10 alphanumeric characters.');

        $this->query->setProductServiceId('abc-123');
    }

    public function testInvoiceHeaderValidation(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceHeader must contain exactly two letters.');

        $this->query->setInvoiceHeader('A1');
    }

    public function testRequestPath(): void
    {
        $content = $this->query
            ->setInvoiceYear((int) date('Y'))
            ->setInvoiceTerm(0)
            ->setUseStatus(0)
            ->setInvoiceCategory(1)
            ->setInvType(InvType::GENERAL)
            ->setProductServiceId('SERVICE1')
            ->setInvoiceHeader('AB')
            ->getContent();

        $this->assertEquals('/B2CInvoice/GetInvoiceWordSetting', $this->query->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }

    /**
     * Helper to fetch raw (unencrypted) content.
     *
     * @return array
     */
    private function getRawContent(): array
    {
        $reflection = new ReflectionClass($this->query);
        $property = $reflection->getProperty('content');
        $property->setAccessible(true);

        return $property->getValue($this->query);
    }
}


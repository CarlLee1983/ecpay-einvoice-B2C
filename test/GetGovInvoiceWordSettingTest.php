<?php

declare(strict_types=1);

use ecPay\eInvoice\Queries\GetGovInvoiceWordSetting;
use PHPUnit\Framework\TestCase;

class GetGovInvoiceWordSettingTest extends TestCase
{
    private GetGovInvoiceWordSetting $query;

    protected function setUp(): void
    {
        $this->query = new GetGovInvoiceWordSetting(
            '2000132',
            'ejCk326UnaZWKisg',
            'q9jcZX8Ib9LM8wYk'
        );
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

    public function testInvoiceYearIsRequired(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceYear cannot be empty.');

        $this->query->getContent();
    }

    public function testSetInvoiceYearWithRocValue(): void
    {
        $this->query->setInvoiceYear('114');
        $content = $this->getRawContent();

        $this->assertSame('114', $content['Data']['InvoiceYear']);
    }

    public function testSetInvoiceYearWithGregorianValue(): void
    {
        $currentYear = (int) date('Y');
        $expectedRoc = str_pad((string) ($currentYear - 1911), 3, '0', STR_PAD_LEFT);

        $this->query->setInvoiceYear($currentYear);
        $content = $this->getRawContent();

        $this->assertSame($expectedRoc, $content['Data']['InvoiceYear']);
    }

    public function testInvoiceYearOutOfRange(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceYear can only target last, current, or next year.');

        $this->query->setInvoiceYear((int) date('Y') - 2);
    }

    public function testRequestPath(): void
    {
        $rocYear = str_pad((string) ((int) date('Y') - 1911), 3, '0', STR_PAD_LEFT);

        $content = $this->query->setInvoiceYear($rocYear)->getContent();

        $this->assertEquals('/B2CInvoice/GetGovInvoiceWordSetting', $this->query->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }
}


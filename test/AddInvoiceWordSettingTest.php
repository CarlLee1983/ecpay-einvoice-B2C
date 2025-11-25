<?php

declare(strict_types=1);

use CarlLee\EcPayB2C\Operations\AddInvoiceWordSetting;
use CarlLee\EcPayB2C\Parameter\InvType;
use PHPUnit\Framework\TestCase;

class AddInvoiceWordSettingTest extends TestCase
{
    private AddInvoiceWordSetting $instance;

    protected function setUp(): void
    {
        $this->instance = new AddInvoiceWordSetting(
            '2000132',
            'ejCk326UnaZWKisg',
            'q9jcZX8Ib9LM8wYk'
        );
    }

    public function testInvoiceTermRange(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceTerm must be between 1 and 6.');

        $this->instance->setInvoiceTerm(0);
    }

    public function testInvoiceHeaderFormat(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceHeader must contain exactly two letters.');

        $this->instance->setInvoiceHeader('A1');
    }

    public function testInvoiceStartSuffix(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceStart must end with 00 or 50.');

        $this->instance->setInvoiceStart('10000010');
    }

    public function testInvoiceEndSuffix(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceEnd must end with 49 or 99.');

        $this->instance->setInvoiceEnd('10000010');
    }

    public function testProductServiceIdValidation(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ProductServiceId must be 1-10 alphanumeric characters.');

        $this->instance->setProductServiceId('abc-123');
    }

    public function testInvoiceYearConversion(): void
    {
        $nextYear = (int) date('Y') + 1;
        $expected = str_pad((string) ($nextYear - 1911), 3, '0', STR_PAD_LEFT);

        $this->instance->setInvoiceYear($nextYear);
        $raw = $this->getRawContent();

        $this->assertSame($expected, $raw['Data']['InvoiceYear']);
    }

    public function testValidationRejectsPastTerm(): void
    {
        $currentTerm = (int) ceil(((int) date('n')) / 2);

        if ($currentTerm === 1) {
            $this->markTestSkipped('Current term is 1; cannot validate earlier term.');
        }

        $this->instance
            ->setInvoiceYear((int) date('Y'))
            ->setInvoiceTerm($currentTerm - 1)
            ->setInvType(InvType::GENERAL)
            ->setInvoiceHeader('AB')
            ->setInvoiceStart('10000000')
            ->setInvoiceEnd('10000049');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceTerm cannot be earlier than the current term.');

        $this->instance->getContent();
    }

    public function testValidationRequiresProperRange(): void
    {
        $this->prepareValidData();
        $this->instance->setInvoiceStart('10000100'); // start is greater than end

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceStart must be less than InvoiceEnd.');

        $this->instance->getContent();
    }

    public function testRequestPath(): void
    {
        $this->prepareValidData();
        $content = $this->instance->getContent();

        $this->assertEquals('/B2CInvoice/AddInvoiceWordSetting', $this->instance->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }

    /**
     * Prepare a valid payload for reuse.
     */
    private function prepareValidData(): void
    {
        $nextYear = (int) date('Y') + 1;

        $this->instance
            ->setInvoiceYear($nextYear)
            ->setInvoiceTerm(1)
            ->setInvType(InvType::GENERAL)
            ->setInvoiceHeader('AB')
            ->setInvoiceStart('10000000')
            ->setInvoiceEnd('10000049')
            ->setProductServiceId('SERVICE1');
    }

    /**
     * Helper to fetch raw (unencrypted) content.
     *
     * @return array
     */
    private function getRawContent(): array
    {
        $reflection = new ReflectionClass($this->instance);
        $property = $reflection->getProperty('content');
        $property->setAccessible(true);

        return $property->getValue($this->instance);
    }
}

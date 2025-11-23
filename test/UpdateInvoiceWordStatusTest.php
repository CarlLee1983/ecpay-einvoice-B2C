<?php

declare(strict_types=1);

use ecPay\eInvoice\Operations\UpdateInvoiceWordStatus;
use PHPUnit\Framework\TestCase;

class UpdateInvoiceWordStatusTest extends TestCase
{
    private UpdateInvoiceWordStatus $operation;

    protected function setUp(): void
    {
        $this->operation = new UpdateInvoiceWordStatus(
            '2000132',
            'ejCk326UnaZWKisg',
            'q9jcZX8Ib9LM8wYk'
        );
    }

    public function testTrackIDRequired(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('TrackID cannot be empty.');

        $this->operation->getContent();
    }

    public function testTrackIDFormat(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('TrackID must be 1-10 alphanumeric characters.');

        $this->operation->setTrackID('abc-123');
    }

    public function testInvoiceStatusRange(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceStatus only supports 0(disable), 1(pause), or 2(enable).');

        $this->operation->setInvoiceStatus(3);
    }

    public function testRequestPath(): void
    {
        $content = $this->operation
            ->setTrackID('1234567890')
            ->setInvoiceStatus(2)
            ->getContent();

        $this->assertEquals('/B2CInvoice/UpdateInvoiceWordStatus', $this->operation->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }
}


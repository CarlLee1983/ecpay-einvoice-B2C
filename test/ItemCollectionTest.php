<?php

use ecPay\eInvoice\DTO\InvoiceItemDto;
use ecPay\eInvoice\DTO\ItemCollection;
use PHPUnit\Framework\TestCase;

class ItemCollectionTest extends TestCase
{
    public function testSumAmount()
    {
        $collection = new ItemCollection([
            InvoiceItemDto::fromArray([
                'name' => 'A',
                'quantity' => 2,
                'unit' => '個',
                'price' => 50,
            ]),
            InvoiceItemDto::fromArray([
                'name' => 'B',
                'quantity' => 1,
                'unit' => '個',
                'price' => 100,
            ]),
        ]);

        $this->assertSame(200.0, $collection->sumAmount());
    }

    public function testToArray()
    {
        $collection = new ItemCollection([
            InvoiceItemDto::fromArray([
                'name' => 'C',
                'quantity' => 1,
                'unit' => '個',
                'price' => 80,
            ]),
        ]);

        $payloads = $collection->toArray();

        $this->assertCount(1, $payloads);
        $this->assertSame('C', $payloads[0]['ItemName']);
    }
}

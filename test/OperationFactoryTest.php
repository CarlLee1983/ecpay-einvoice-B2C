<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Tests;

use ecPay\eInvoice\Factories\OperationFactory;
use ecPay\eInvoice\Operations\Invoice;
use ecPay\eInvoice\Operations\InvalidInvoice;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * OperationFactory 單元測試，確認別名與初始化邏輯可正常運作。
 */
class OperationFactoryTest extends TestCase
{
    protected function makeFactory(): OperationFactory
    {
        return new OperationFactory([
            'merchant_id' => '2000132',
            'hash_key' => 'HashKey',
            'hash_iv' => 'HashIV',
        ]);
    }

    public function testMakeInvoiceFromShortAlias(): void
    {
        $factory = $this->makeFactory();

        $invoice = $factory->make('invoice');

        $this->assertInstanceOf(Invoice::class, $invoice);
    }

    public function testCustomAlias(): void
    {
        $factory = $this->makeFactory();
        $factory->alias('custom.invalid', InvalidInvoice::class);

        $instance = $factory->make('custom.invalid');

        $this->assertInstanceOf(InvalidInvoice::class, $instance);
    }

    public function testInitializerRunsForEveryInstance(): void
    {
        $factory = $this->makeFactory();

        $factory->addInitializer(function (Invoice $invoice) {
            $invoice->setRelateNumber('INIT123');
        });

        $invoice = $factory->make('invoice');

        $reflection = new ReflectionClass($invoice);
        $property = $reflection->getProperty('content');
        $property->setAccessible(true);
        $content = $property->getValue($invoice);

        $this->assertSame('INIT123', $content['Data']['RelateNumber']);
    }
}



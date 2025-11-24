<?php

use ecPay\eInvoice\DTO\InvoiceItemDto;

class InvalidInvoiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        $this->client = new ecPay\eInvoice\EcPayClient(
            $_ENV['SERVER'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
        $this->instance = new ecPay\eInvoice\Operations\InvalidInvoice(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
    }

    /**
     * @return InvoiceItemDto[]
     */
    private function buildInvoiceItems(): array
    {
        return [
            InvoiceItemDto::fromArray([
                'name' => '商品範例',
                'quantity' => 1,
                'unit' => '個',
                'price' => 100,
            ]),
        ];
    }

    public function testQuickCheck()
    {
        $relateNumber = 'YEP' . date('YmdHis');
        $invoice = new ecPay\eInvoice\Operations\Invoice(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
        $invoice->setRelateNumber($relateNumber)
            ->setCustomerEmail('cylee@chyp.com.tw')
            ->setItems($this->buildInvoiceItems())
            ->setSalesAmount(100);

        $response = $this->client->send($invoice);

        if ($response->success()) {
            $data = $response->getData();

            $this->instance->setRelateNumber($relateNumber)
                ->setInvoiceNo($data['InvoiceNo'])
                ->setInvoiceDate(date('Y-m-d', strtotime($data['InvoiceDate'])))
                ->setReason('Cancel Order.');

            $response = $this->client->send($this->instance);

            $this->assertTrue($response->success());
        }
    }
}

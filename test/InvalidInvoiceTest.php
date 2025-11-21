<?php

class InvalidInvoiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->client = new ecPay\eInvoice\EcPayClient(
            $_ENV['SERVER'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
        $this->instance = new ecPay\eInvoice\InvalidInvoice(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
    }

    public function testQuickCheck()
    {
        $relateNumber = 'YEP' . date('YmdHis');
        $invoice = new ecPay\eInvoice\Invoice(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
        $invoice->setRelateNumber($relateNumber)
            ->setCustomerEmail('cylee@chyp.com.tw')
            ->setItems([
                [
                    'name' => '商品範例',
                    'quantity' => 1,
                    'unit' => '個',
                    'price' => 100,
                    'totalPrice' => 100,
                ],
            ])
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

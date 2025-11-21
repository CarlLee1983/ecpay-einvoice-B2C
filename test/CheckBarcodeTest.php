<?php

class CheckBarcodeTest extends \PHPUnit\Framework\TestCase
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
        $this->instance = new ecPay\eInvoice\CheckBarcode(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
    }

    public function testQuickCheck()
    {
        $this->instance->setBarcode($_ENV['BARCODE']);
        $response = $this->client->send($this->instance);

        $this->assertTrue($response->success());
    }
}

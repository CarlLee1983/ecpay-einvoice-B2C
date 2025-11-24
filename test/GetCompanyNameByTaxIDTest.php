<?php

class GetCompanyNameByTaxIDTest extends \PHPUnit\Framework\TestCase
{
    /**
     * 建立測試所需的基本物件。
     */
    protected function setUp(): void
    {
        $this->client = new ecPay\eInvoice\EcPayClient(
            $_ENV['SERVER'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );

        $this->instance = new ecPay\eInvoice\Queries\GetCompanyNameByTaxID(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
    }

    public function testQuickCheck()
    {
        $this->instance->setUnifiedBusinessNo($_ENV['UNIFIED_BUSINESS_NO']);
        $response = $this->client->send($this->instance);

        $this->assertTrue($response->success());
    }
}

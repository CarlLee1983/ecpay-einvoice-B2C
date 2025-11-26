<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Tests\Integration;

use CarlLee\EcPayB2C\Operations\Invoice;

/**
 * 發票開立整合測試。
 *
 * 這些測試會實際呼叫 ECPay 測試伺服器，需要網路連線。
 *
 * @group integration
 */
class InvoiceIntegrationTest extends IntegrationTestCase
{
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoice = new Invoice(
            $this->merchantId,
            $this->hashKey,
            $this->hashIV
        );
    }

    /**
     * 測試一般發票開立（會員載具）。
     */
    public function testIssueInvoiceWithMemberCarrier(): void
    {
        $this->invoice
            ->setRelateNumber($this->generateRelateNumber())
            ->setCarrierType('1')
            ->setCustomerEmail('test@example.com')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $response = $this->client->send($this->invoice);

        $this->assertTrue($response->success(), '發票開立失敗: ' . $response->getMessage());
    }

    /**
     * 測試手機條碼載具發票開立。
     */
    public function testIssueInvoiceWithCellphoneCarrier(): void
    {
        $barcode = $_ENV['BARCODE'] ?? '/YC+RROR';

        $this->invoice
            ->setRelateNumber($this->generateRelateNumber())
            ->setCarrierType('3')
            ->setCarrierNum($barcode)
            ->setCustomerEmail('test@example.com')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $response = $this->client->send($this->invoice);

        $this->assertTrue($response->success(), '發票開立失敗: ' . $response->getMessage());
    }

    /**
     * 測試含統編的發票開立。
     */
    public function testIssueInvoiceWithIdentifier(): void
    {
        $this->invoice
            ->setRelateNumber($this->generateRelateNumber())
            ->setCustomerIdentifier('28465676')
            ->setCustomerName('測試公司')
            ->setCustomerAddr('測試地址')
            ->setCustomerEmail('test@example.com')
            ->setPrintMark('1')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $response = $this->client->send($this->invoice);

        $this->assertTrue($response->success(), '發票開立失敗: ' . $response->getMessage());
    }

    /**
     * 測試捐贈發票開立。
     */
    public function testIssueInvoiceWithDonation(): void
    {
        $loveCode = $_ENV['LOVECODE'] ?? '9999';

        $this->invoice
            ->setRelateNumber($this->generateRelateNumber())
            ->setCustomerEmail('test@example.com')
            ->setDonation('1')
            ->setLoveCode($loveCode)
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $response = $this->client->send($this->invoice);

        $this->assertTrue($response->success(), '發票開立失敗: ' . $response->getMessage());
    }

    /**
     * 測試多商品發票開立。
     */
    public function testIssueInvoiceWithMultipleItems(): void
    {
        $this->invoice
            ->setRelateNumber($this->generateRelateNumber())
            ->setCarrierType('1')
            ->setCustomerEmail('test@example.com')
            ->setItems($this->makeItems([
                ['name' => '商品A', 'quantity' => 2, 'unit' => '個', 'price' => 100],
                ['name' => '商品B', 'quantity' => 1, 'unit' => '件', 'price' => 300],
                ['name' => '商品C', 'quantity' => 5, 'unit' => '組', 'price' => 50],
            ]))
            ->setSalesAmount(750);

        $response = $this->client->send($this->invoice);

        $this->assertTrue($response->success(), '發票開立失敗: ' . $response->getMessage());
    }
}


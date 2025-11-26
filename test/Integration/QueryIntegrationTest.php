<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Tests\Integration;

use CarlLee\EcPayB2C\Operations\Invoice;
use CarlLee\EcPayB2C\Operations\InvalidInvoice;
use CarlLee\EcPayB2C\Queries\CheckBarcode;
use CarlLee\EcPayB2C\Queries\CheckLoveCode;
use CarlLee\EcPayB2C\Queries\GetCompanyNameByTaxID;
use CarlLee\EcPayB2C\Queries\GetInvoice;

/**
 * 查詢 API 整合測試。
 *
 * 這些測試會實際呼叫 ECPay 測試伺服器，需要網路連線。
 *
 * @group integration
 */
class QueryIntegrationTest extends IntegrationTestCase
{
    /**
     * 測試驗證手機條碼。
     */
    public function testCheckBarcode(): void
    {
        $barcode = $_ENV['BARCODE'] ?? '/YC+RROR';

        $query = new CheckBarcode($this->merchantId, $this->hashKey, $this->hashIV);
        $query->setBarcode($barcode);

        $response = $this->client->send($query);

        $this->assertTrue($response->success(), '驗證手機條碼失敗: ' . $response->getMessage());
    }

    /**
     * 測試驗證愛心碼。
     */
    public function testCheckLoveCode(): void
    {
        $loveCode = $_ENV['LOVECODE'] ?? '9527';

        $query = new CheckLoveCode($this->merchantId, $this->hashKey, $this->hashIV);
        $query->setLoveCode($loveCode);

        $response = $this->client->send($query);

        $this->assertTrue($response->success(), '驗證愛心碼失敗: ' . $response->getMessage());
    }

    /**
     * 測試查詢公司名稱。
     */
    public function testGetCompanyNameByTaxID(): void
    {
        $taxId = $_ENV['UNIFIED_BUSINESS_NO'] ?? '97025978';

        $query = new GetCompanyNameByTaxID($this->merchantId, $this->hashKey, $this->hashIV);
        $query->setUnifiedBusinessNo($taxId);

        $response = $this->client->send($query);

        $this->assertTrue($response->success(), '查詢公司名稱失敗: ' . $response->getMessage());
    }

    /**
     * 測試開立並查詢發票。
     */
    public function testIssueAndQueryInvoice(): void
    {
        // 先開立發票
        $relateNumber = $this->generateRelateNumber();
        $invoice = new Invoice($this->merchantId, $this->hashKey, $this->hashIV);
        $invoice
            ->setRelateNumber($relateNumber)
            ->setCarrierType('1')
            ->setCustomerEmail('test@example.com')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $issueResponse = $this->client->send($invoice);

        if (!$issueResponse->success()) {
            $this->markTestSkipped('無法開立發票以進行查詢測試');
        }

        $data = $issueResponse->getData();

        // 查詢發票
        $query = new GetInvoice($this->merchantId, $this->hashKey, $this->hashIV);
        $query
            ->setRelateNumber($relateNumber)
            ->setInvoiceNo($data['InvoiceNo'])
            ->setInvoiceDate(date('Y-m-d', strtotime($data['InvoiceDate'])));

        $queryResponse = $this->client->send($query);

        $this->assertTrue($queryResponse->success(), '查詢發票失敗: ' . $queryResponse->getMessage());
    }

    /**
     * 測試開立並作廢發票。
     */
    public function testIssueAndInvalidInvoice(): void
    {
        // 先開立發票
        $relateNumber = $this->generateRelateNumber();
        $invoice = new Invoice($this->merchantId, $this->hashKey, $this->hashIV);
        $invoice
            ->setRelateNumber($relateNumber)
            ->setCarrierType('1')
            ->setCustomerEmail('test@example.com')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $issueResponse = $this->client->send($invoice);

        if (!$issueResponse->success()) {
            $this->markTestSkipped('無法開立發票以進行作廢測試');
        }

        $data = $issueResponse->getData();

        // 作廢發票
        $invalid = new InvalidInvoice($this->merchantId, $this->hashKey, $this->hashIV);
        $invalid
            ->setRelateNumber($relateNumber)
            ->setInvoiceNo($data['InvoiceNo'])
            ->setInvoiceDate(date('Y-m-d', strtotime($data['InvoiceDate'])))
            ->setReason('測試作廢');

        $invalidResponse = $this->client->send($invalid);

        $this->assertTrue($invalidResponse->success(), '作廢發票失敗: ' . $invalidResponse->getMessage());
    }
}

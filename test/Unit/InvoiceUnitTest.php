<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Tests\Unit;

use CarlLee\EcPayB2C\EcPayClient;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;
use CarlLee\EcPayB2C\Operations\Invoice;
use CarlLee\EcPayB2C\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * 發票開立單元測試。
 *
 * 這些測試驗證業務邏輯，不需要網路連線。
 *
 * @group unit
 */
class InvoiceUnitTest extends UnitTestCase
{
    private Invoice $invoice;
    private EcPayClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoice = new Invoice(
            $this->merchantId,
            $this->hashKey,
            $this->hashIV
        );

        $this->client = new EcPayClient(
            'https://einvoice-stage.ecpay.com.tw',
            $this->hashKey,
            $this->hashIV
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Request::setHttpClient(null);
    }

    /**
     * 測試缺少 RelateNumber 時拋出驗證例外。
     */
    public function testThrowsValidationExceptionWhenMissingRelateNumber(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The invoice RelateNumber is empty.');

        $this->invoice
            ->setCustomerEmail('test@example.com')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->invoice);
    }

    /**
     * 測試缺少客戶聯絡資訊時拋出驗證例外。
     */
    public function testThrowsValidationExceptionWhenMissingCustomerContact(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('You should be settings either of customer phone and email.');

        $this->invoice
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->invoice);
    }

    /**
     * 測試銷售金額不匹配時拋出驗證例外。
     */
    public function testThrowsValidationExceptionWhenSalesAmountMismatch(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The calculated sales amount is not equal to the set sales amount.');

        $this->invoice
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCustomerEmail('test@example.com')
            ->setItems($this->makeItems())
            ->setSalesAmount(500);

        $this->client->send($this->invoice);
    }

    /**
     * 測試捐贈但缺少愛心碼時拋出驗證例外。
     */
    public function testThrowsValidationExceptionWhenDonationWithoutLoveCode(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Donation is yes, love code required.');

        $this->invoice
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCustomerEmail('test@example.com')
            ->setDonation('1')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->invoice);
    }

    /**
     * 測試統編與捐贈衝突時拋出驗證例外。
     */
    public function testThrowsValidationExceptionWhenIdentifierWithDonation(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Customer identifier not empty, donation can not be yes.');

        $this->invoice
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCustomerIdentifier('12345678')
            ->setCustomerName('測試公司')
            ->setCustomerAddr('測試地址')
            ->setCustomerEmail('test@example.com')
            ->setPrintMark('1')
            ->setDonation('1')
            ->setLoveCode('123456')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->invoice);
    }

    /**
     * 測試載具與列印衝突時拋出驗證例外。
     */
    public function testThrowsValidationExceptionWhenCarrierWithPrintMark(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Carrier type is not empty, invoice can not be print.');

        $this->invoice
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCustomerName('測試客戶')
            ->setCustomerAddr('測試地址')
            ->setCustomerEmail('test@example.com')
            ->setPrintMark('1')
            ->setCarrierType('1')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->invoice);
    }

    /**
     * 測試手機條碼長度錯誤時拋出驗證例外。
     */
    public function testThrowsValidationExceptionWhenInvalidCellphoneCarrier(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invoice carrier type is Cellphone, carrier number length must be 8.');

        $this->invoice
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCustomerEmail('test@example.com')
            ->setCarrierType('3')
            ->setCarrierNum('123')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->invoice);
    }

    /**
     * 測試無效的載具類型時拋出參數例外。
     */
    public function testThrowsInvalidParameterExceptionWhenInvalidCarrierType(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Invoice carrier type format is wrong.');

        $this->invoice->setCarrierType('99');
    }

    /**
     * 測試無效的稅別時拋出參數例外。
     */
    public function testThrowsInvalidParameterExceptionWhenInvalidTaxType(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Invoice tax type format is invalid.');

        $this->invoice->setTaxType('99');
    }

    /**
     * 測試無效的列印標記時拋出參數例外。
     */
    public function testThrowsInvalidParameterExceptionWhenInvalidPrintMark(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Invoice print mark format is wrong.');

        $this->invoice->setPrintMark('99');
    }

    /**
     * 測試愛心碼長度錯誤時拋出參數例外。
     */
    public function testThrowsInvalidParameterExceptionWhenInvalidLoveCode(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Invoice love code is wrong.');

        $this->invoice->setLoveCode('12');
    }

    /**
     * 測試銷售金額無效時拋出參數例外。
     */
    public function testThrowsInvalidParameterExceptionWhenInvalidSalesAmount(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Invoice sales amount is invalid.');

        $this->invoice->setSalesAmount(0);
    }

    /**
     * 測試 Fluent Interface 回傳型別。
     */
    public function testFluentInterfaceReturnsSelf(): void
    {
        $result = $this->invoice
            ->setRelateNumber('TEST123')
            ->setCustomerEmail('test@example.com')
            ->setCarrierType('1');

        $this->assertInstanceOf(Invoice::class, $result);
    }

    /**
     * 測試正確的發票資料可以通過驗證。
     */
    public function testValidInvoicePassesValidation(): void
    {
        // 使用 Mock 來避免實際的 HTTP 呼叫
        $mockResponse = new Response(200, [], json_encode([
            'TransCode' => 1,
            'TransMsg' => 'Success',
            'Data' => base64_encode(openssl_encrypt(
                urlencode(json_encode([
                    'RtnCode' => 1,
                    'RtnMsg' => 'Success',
                    'InvoiceNo' => 'AB12345678',
                    'InvoiceDate' => date('Y-m-d'),
                ])),
                'AES-128-CBC',
                $this->hashKey,
                OPENSSL_RAW_DATA,
                $this->hashIV
            )),
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        Request::setHttpClient($httpClient);

        $this->invoice
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCarrierType('1')
            ->setCustomerEmail('test@example.com')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $response = $this->client->send($this->invoice);

        $this->assertTrue($response->success());
    }
}


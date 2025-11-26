<?php

use CarlLee\EcPayB2C\DTO\InvoiceItemDto;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

/**
 * 發票測試類別。
 *
 * 注意：此檔案包含整合測試（需要網路連線）。
 * 建議使用 test/Unit/InvoiceUnitTest.php 進行單元測試，
 * 或使用 test/Integration/InvoiceIntegrationTest.php 進行整合測試。
 *
 * @deprecated 將在未來版本移除，請改用 Unit/InvoiceUnitTest 和 Integration/InvoiceIntegrationTest
 */
class InvoiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        $this->client = new CarlLee\EcPayB2C\EcPayClient(
            $_ENV['SERVER'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
        $this->instance = new CarlLee\EcPayB2C\Operations\Invoice(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
    }

    /**
     * @param array<int,array<string,mixed>> $items
     * @return InvoiceItemDto[]
     */
    private function makeItems(array $items = []): array
    {
        if ($items === []) {
            $items = [
                [
                    'name' => '商品範例',
                    'quantity' => 1,
                    'unit' => '個',
                    'price' => 100,
                ],
            ];
        }

        return array_map(
            static fn (array $item): InvoiceItemDto => InvoiceItemDto::fromArray($item),
            $items
        );
    }

    public function testQuickCreate()
    {
        $this->instance->setRelateNumber('YEP' . date('YmdHis') . rand(10, 99))
            ->setCarrierType('1')
            ->setCustomerEmail('cylee@chyp.com.tw')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $response = $this->client->send($this->instance);

        $this->assertTrue($response->success());
    }

    public function testCarrier()
    {
        $this->instance->setRelateNumber('YEP' . date('YmdHis') . rand(10, 99))
            ->setCarrierType('3')
            ->setCarrierNum('/YC+RROR')
            ->setCustomerEmail('cylee@chyp.com.tw')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $response = $this->client->send($this->instance);

        $this->assertTrue($response->success());
    }

    public function testIdentifier()
    {
        $this->instance->setRelateNumber('YEP' . date('YmdHis') . rand(10, 99))
            ->setCustomerIdentifier('28465676')
            ->setCustomerName('中華國際黃頁股份有限公司')
            ->setCustomerAddr('台中市北區進化路322號')
            ->setCustomerEmail('cylee@chyp.com.tw')
            ->setPrintMark('1')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $response = $this->client->send($this->instance);

        $this->assertTrue($response->success());
    }

    /**
     * 測試錯誤情境 - 缺少 RelateNumber
     */
    public function testErrorMissingRelateNumber()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The invoice RelateNumber is empty.');

        $this->instance
            ->setCustomerEmail('test@example.com')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->instance);
    }

    /**
     * 測試錯誤情境 - 缺少客戶聯絡資訊
     */
    public function testErrorMissingCustomerContact()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('You should be settings either of customer phone and email.');

        $this->instance
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->instance);
    }

    /**
     * 測試錯誤情境 - 商品項目為空
     */
    public function testErrorEmptyItems()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The calculated sales amount is not equal to the set sales amount.');

        $this->instance
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCustomerEmail('test@example.com')
            ->setSalesAmount(100);

        $this->client->send($this->instance);
    }

    /**
     * 測試錯誤情境 - 銷售金額不匹配
     */
    public function testErrorSalesAmountMismatch()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The calculated sales amount is not equal to the set sales amount.');

        $this->instance
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCustomerEmail('test@example.com')
            ->setItems($this->makeItems())
            ->setSalesAmount(500); // 設定錯誤的金額

        $this->client->send($this->instance);
    }

    /**
     * 測試錯誤情境 - 捐贈但缺少愛心碼
     */
    public function testErrorDonationWithoutLoveCode()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Donation is yes, love code required.');

        $this->instance
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCustomerEmail('test@example.com')
            ->setDonation('1')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->instance);
    }

    /**
     * 測試錯誤情境 - 統編與捐贈衝突
     */
    public function testErrorIdentifierWithDonation()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Customer identifier not empty, donation can not be yes.');

        $this->instance
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

        $this->client->send($this->instance);
    }

    /**
     * 測試錯誤情境 - 載具與列印衝突
     */
    public function testErrorCarrierWithPrintMark()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Carrier type is not empty, invoice can not be print.');

        $this->instance
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCustomerName('測試客戶')
            ->setCustomerAddr('測試地址')
            ->setCustomerEmail('test@example.com')
            ->setPrintMark('1')
            ->setCarrierType('1')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->instance);
    }

    /**
     * 測試錯誤情境 - 手機條碼格式錯誤
     */
    public function testErrorInvalidCellphoneCarrier()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invoice carrier type is Cellphone, carrier number length must be 8.');

        $this->instance
            ->setRelateNumber('TEST' . date('YmdHis'))
            ->setCustomerEmail('test@example.com')
            ->setCarrierType('3')
            ->setCarrierNum('123') // 錯誤長度
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $this->client->send($this->instance);
    }

    /**
     * 測試 Fluent Interface 回傳型別
     */
    public function testFluentInterfaceReturnsSelf()
    {
        $result = $this->instance
            ->setRelateNumber('TEST123')
            ->setCustomerEmail('test@example.com')
            ->setCarrierType('1');

        $this->assertInstanceOf(CarlLee\EcPayB2C\Operations\Invoice::class, $result);
    }

    /**
     * 測試設定多個商品項目
     */
    public function testMultipleItems()
    {
        $this->instance->setRelateNumber('YEP' . date('YmdHis') . rand(10, 99))
            ->setCarrierType('1')
            ->setCustomerEmail('cylee@chyp.com.tw')
            ->setItems($this->makeItems([
                [
                    'name' => '商品A',
                    'quantity' => 2,
                    'unit' => '個',
                    'price' => 100,
                    'totalPrice' => 200,
                ],
                [
                    'name' => '商品B',
                    'quantity' => 1,
                    'unit' => '件',
                    'price' => 300,
                    'totalPrice' => 300,
                ],
                [
                    'name' => '商品C',
                    'quantity' => 5,
                    'unit' => '組',
                    'price' => 50,
                    'totalPrice' => 250,
                ],
            ]))
            ->setSalesAmount(750);

        $response = $this->client->send($this->instance);

        $this->assertTrue($response->success());
    }

    /**
     * 測試捐贈發票
     */
    public function testDonationInvoice()
    {
        $this->instance->setRelateNumber('YEP' . date('YmdHis') . rand(10, 99))
            ->setCustomerEmail('cylee@chyp.com.tw')
            ->setDonation('1')
            ->setLoveCode('9999')
            ->setItems($this->makeItems())
            ->setSalesAmount(100);

        $response = $this->client->send($this->instance);

        $this->assertTrue($response->success());
    }
}

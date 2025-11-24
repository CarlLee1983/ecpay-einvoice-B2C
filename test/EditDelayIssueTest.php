<?php

use ecPay\eInvoice\DTO\InvoiceItemDto;

class EditDelayIssueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * 測試用憑證資訊。
     *
     * @var array
     */
    private $credentials = [];

    protected function setUp(): void
    {
        $this->credentials = [
            'merchantId' => $_ENV['MERCHANT_ID'],
            'hashKey' => $_ENV['HASH_KEY'],
            'hashIV' => $_ENV['HASH_IV'],
        ];
    }

    public function testValidationRequiresTsr()
    {
        $instance = $this->buildBaseInvoice()
            ->setDelayFlag('1')
            ->setDelayDay(3);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('編輯延遲開立發票時必須帶入 Tsr。');

        $instance->getContent();
    }

    public function testRequestPathAndPayload()
    {
        $instance = $this->buildBaseInvoice()
            ->setDelayFlag('1')
            ->setDelayDay(3)
            ->setTsr('TSR' . date('His'));

        $content = $instance->getContent();

        $this->assertEquals('/B2CInvoice/EditDelayIssue', $instance->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }

    /**
     * 產生具備基本欄位的延遲發票物件。
     *
     * @return ecPay\eInvoice\Operations\EditDelayIssue
     */
    private function buildBaseInvoice(): ecPay\eInvoice\Operations\EditDelayIssue
    {
        return $this->makeEditDelayIssue()
            ->setRelateNumber('EDIT' . date('YmdHis'))
            ->setCustomerEmail('test@example.com')
            ->setItems($this->buildItems())
            ->setSalesAmount(100);
    }

    /**
     * 建立 EditDelayIssue 實例。
     *
     * @return ecPay\eInvoice\Operations\EditDelayIssue
     */
    private function makeEditDelayIssue(): ecPay\eInvoice\Operations\EditDelayIssue
    {
        return new ecPay\eInvoice\Operations\EditDelayIssue(
            $this->credentials['merchantId'],
            $this->credentials['hashKey'],
            $this->credentials['hashIV']
        );
    }

    /**
     * @return InvoiceItemDto[]
     */
    private function buildItems(): array
    {
        return [
            InvoiceItemDto::fromArray([
                'name' => '編輯延遲測試商品',
                'quantity' => 1,
                'unit' => '組',
                'price' => 100,
            ]),
        ];
    }
}

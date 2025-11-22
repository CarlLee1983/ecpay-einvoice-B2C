<?php

class DelayIssueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * 測試環境用共用參數。
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

    public function testDelayFlagRejectsInvalidValue()
    {
        $instance = $this->makeDelayIssue();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('DelayFlag 僅能為 1(延遲) 或 2(觸發)。');

        $instance->setDelayFlag('9');
    }

    public function testValidationRequiresDelayFlag()
    {
        $instance = $this->buildBaseInvoice();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('DelayFlag 不可為空。');

        $instance->getContent();
    }

    public function testTriggerModeRequiresTsr()
    {
        $instance = $this->buildBaseInvoice()
            ->setDelayFlag('2')
            ->setDelayDay(2)
            ->setPayType('2')
            ->setPayAct('123456789012345');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('觸發開立時 Tsr 為必填。');

        $instance->getContent();
    }

    public function testScheduleModeBuildsPayload()
    {
        $instance = $this->buildBaseInvoice()
            ->setDelayFlag('1')
            ->setDelayDay(3);

        $content = $instance->getContent();

        $this->assertEquals('/B2CInvoice/DelayIssue', $instance->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }

    /**
     * 產生具備基本欄位的延遲開立請求。
     *
     * @return ecPay\eInvoice\Operations\DelayIssue
     */
    private function buildBaseInvoice(): ecPay\eInvoice\Operations\DelayIssue
    {
        return $this->makeDelayIssue()
            ->setRelateNumber('DELAY' . date('YmdHis'))
            ->setCustomerEmail('test@example.com')
            ->setItems([
                [
                    'name' => '延遲開立測試商品',
                    'quantity' => 1,
                    'unit' => '組',
                    'price' => 100,
                    'totalPrice' => 100,
                ],
            ])
            ->setSalesAmount(100);
    }

    /**
     * 建立 DelayIssue 實例。
     *
     * @return ecPay\eInvoice\Operations\DelayIssue
     */
    private function makeDelayIssue(): ecPay\eInvoice\Operations\DelayIssue
    {
        return new ecPay\eInvoice\Operations\DelayIssue(
            $this->credentials['merchantId'],
            $this->credentials['hashKey'],
            $this->credentials['hashIV']
        );
    }
}


<?php

class TriggerIssueTest extends \PHPUnit\Framework\TestCase
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

    public function testPayTypeMustBeTwo()
    {
        $instance = $this->makeTriggerIssue();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('PayType 僅支援 2。');

        $instance->setPayType('1');
    }

    public function testValidationRequiresTsr()
    {
        $instance = $this->makeTriggerIssue()
            ->setPayType('2');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tsr 不可為空。');

        $instance->getContent();
    }

    public function testRequestPath()
    {
        $instance = $this->makeTriggerIssue()
            ->setPayType('2')
            ->setTsr('TSR' . date('His'));

        $content = $instance->getContent();

        $this->assertEquals('/B2CInvoice/TriggerIssue', $instance->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }

    /**
     * 建立 TriggerIssue 實例。
     *
     * @return ecPay\eInvoice\Operations\TriggerIssue
     */
    private function makeTriggerIssue(): ecPay\eInvoice\Operations\TriggerIssue
    {
        return new ecPay\eInvoice\Operations\TriggerIssue(
            $this->credentials['merchantId'],
            $this->credentials['hashKey'],
            $this->credentials['hashIV']
        );
    }
}


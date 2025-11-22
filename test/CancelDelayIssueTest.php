<?php

class CancelDelayIssueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * 測試用憑證。
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
        $instance = $this->makeCancelDelayIssue();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tsr 不可為空。');

        $instance->getContent();
    }

    public function testRequestPath()
    {
        $instance = $this->makeCancelDelayIssue()
            ->setTsr('TSR' . date('His'));

        $content = $instance->getContent();

        $this->assertEquals('/B2CInvoice/CancelDelayIssue', $instance->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }

    /**
     * 建立 CancelDelayIssue 實例。
     */
    private function makeCancelDelayIssue(): ecPay\eInvoice\Operations\CancelDelayIssue
    {
        return new ecPay\eInvoice\Operations\CancelDelayIssue(
            $this->credentials['merchantId'],
            $this->credentials['hashKey'],
            $this->credentials['hashIV']
        );
    }
}


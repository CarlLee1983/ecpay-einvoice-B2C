<?php

class GetIssueListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * 測試用憑證。
     *
     * @var array
     */
    private $credentials = [];

    /**
     * @var CarlLee\EcPayB2C\Queries\GetIssueList
     */
    private $instance;

    protected function setUp(): void
    {
        $this->credentials = [
            'merchantId' => $_ENV['MERCHANT_ID'],
            'hashKey' => $_ENV['HASH_KEY'],
            'hashIV' => $_ENV['HASH_IV'],
        ];

        $this->instance = new CarlLee\EcPayB2C\Queries\GetIssueList(
            $this->credentials['merchantId'],
            $this->credentials['hashKey'],
            $this->credentials['hashIV']
        );
    }

    public function testInvalidDateFormat()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('日期格式需為 yyyy-MM-dd 或 yyyy/MM/dd。');

        $this->instance->setBeginDate('20240135');
    }

    public function testNumPerPageUpperBound()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('NumPerPage 必須介於 1 到 200。');

        $this->instance->setNumPerPage(500);
    }

    public function testValidationRequiresDates()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('BeginDate 不可為空。');

        $this->instance->getContent();
    }

    public function testRequestPath()
    {
        $content = $this->instance
            ->setBeginDate('2024-01-01')
            ->setEndDate('2024-01-31')
            ->setNumPerPage(100)
            ->setShowingPage(2)
            ->setFormat('2')
            ->getContent();

        $this->assertEquals('/B2CInvoice/GetIssueList', $this->instance->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }
}

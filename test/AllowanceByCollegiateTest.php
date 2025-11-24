<?php

use ecPay\eInvoice\DTO\AllowanceCollegiateItemDto;

class AllowanceByCollegiateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ecPay\eInvoice\Operations\AllowanceByCollegiate
     */
    private $instance;

    protected function setUp(): void
    {
        $this->instance = new ecPay\eInvoice\Operations\AllowanceByCollegiate(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
    }

    /**
     * @param array<int,array<string,mixed>> $items
     * @return AllowanceCollegiateItemDto[]
     */
    private function makeItems(array $items = []): array
    {
        if ($items === []) {
            $items = [
                [
                    'name' => '折讓商品',
                    'quantity' => 1,
                    'unit' => '組',
                    'price' => 100,
                    'taxType' => '1',
                ],
            ];
        }

        return array_map(
            static fn(array $item): AllowanceCollegiateItemDto => AllowanceCollegiateItemDto::fromArray($item),
            $items
        );
    }

    public function testInvoiceNoFormat()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('InvoiceNo 格式錯誤，需為 2 碼英文 + 8 碼數字。');

        $this->instance->setInvoiceNo('1234567890');
    }

    public function testInvalidNotifyMail()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('NotifyMail 格式錯誤。');

        $this->instance->setNotifyMail('not-email');
    }

    public function testItemsRequiredFields()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('折讓項目缺少欄位: taxType');

        $this->instance->setItems([
            [
                'name' => 'test',
                'quantity' => 1,
                'unit' => '個',
                'price' => 10,
            ],
        ]);
    }

    public function testValidationRequiresReturnURL()
    {
        $this->instance->setInvoiceNo('UV11100016')
            ->setInvoiceDate('2024-01-10')
            ->setCustomerName('王小明')
            ->setNotifyMail('buyer@example.com')
            ->setItems($this->makeItems());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ReturnURL 不可為空。');

        $this->instance->getContent();
    }

    public function testRequestPath()
    {
        $content = $this->instance->setInvoiceNo('UV11100016')
            ->setInvoiceDate('2024-01-10')
            ->setCustomerName('王小明')
            ->setNotifyMail('buyer@example.com')
            ->setReturnURL('https://example.com/callback')
            ->setItems($this->makeItems())
            ->getContent();

        $this->assertEquals('/B2CInvoice/AllowanceByCollegiate', $this->instance->getRequestPath());
        $this->assertArrayHasKey('Data', $content);
    }
}


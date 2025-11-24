<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\DTO\AllowanceCollegiateItemDto;
use ecPay\eInvoice\Operations\AllowanceByCollegiate;

$invoiceNo = 'UV11100016';
$invoiceDate = '2024-01-10';

echo "建立線上折讓 (InvoiceNo: {$invoiceNo})...\n";

$allowance = new AllowanceByCollegiate($merchantId, $hashKey, $hashIV);
$allowance->setInvoiceNo($invoiceNo)
    ->setInvoiceDate($invoiceDate)
    ->setCustomerName('王小明')
    ->setNotifyMail('buyer@example.com')
    ->setReturnURL('https://example.com/allowance/callback')
    ->setItems([
        AllowanceCollegiateItemDto::fromArray([
            'name' => '折讓商品',
            'quantity' => 1,
            'unit' => '組',
            'price' => 100,
            'taxType' => '1',
        ]),
    ]);

try {
    $response = $client->send($allowance);
    $data = $response->getData();
    printResult($data);

    if ($data['RtnCode'] == 1) {
        echo "線上折讓建立完成，等待買家點擊同意通知。\n";
    }
} catch (Exception $e) {
    echo '線上折讓建立失敗：' . $e->getMessage() . PHP_EOL;
}


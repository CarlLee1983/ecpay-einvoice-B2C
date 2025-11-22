<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\Operations\DelayIssue;

$relateNumber = 'DELAY' . date('YmdHis');

echo "準備建立延遲開立發票 (RelateNumber: {$relateNumber})...\n";

$delayIssue = new DelayIssue($merchantId, $hashKey, $hashIV);

$delayIssue->setRelateNumber($relateNumber)
    ->setCustomerEmail('buyer@example.com')
    ->setItems([
        [
            'name' => '延遲開立測試商品',
            'quantity' => 1,
            'unit' => '組',
            'price' => 100,
            'totalPrice' => 100,
        ],
    ])
    ->setSalesAmount(100)
    ->setDelayFlag('1') // 1: 預約自動開立，2: 後續觸發開立
    ->setDelayDay(3);   // 3 天後自動開立

try {
    $response = $client->send($delayIssue);
    $data = $response->getData();
    printResult($data);

    if ($data['RtnCode'] == 1) {
        echo "延遲開立已建立，等待系統於指定天數後開立。\n";
    }
} catch (Exception $e) {
    echo '延遲開立發票失敗：' . $e->getMessage() . PHP_EOL;
}


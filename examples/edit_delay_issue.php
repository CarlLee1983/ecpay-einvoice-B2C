<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\Operations\EditDelayIssue;

$tsr = '請填入原延遲開立取得的TSR';
$relateNumber = 'EDIT' . date('YmdHis');

echo "正在編輯延遲開立發票 (RelateNumber: {$relateNumber})...\n";

$editDelayIssue = new EditDelayIssue($merchantId, $hashKey, $hashIV);

$editDelayIssue->setRelateNumber($relateNumber)
    ->setCustomerEmail('buyer@example.com')
    ->setItems([
        [
            'name' => '調整後商品',
            'quantity' => 2,
            'unit' => '組',
            'price' => 150,
            'totalPrice' => 300,
        ],
    ])
    ->setSalesAmount(300)
    ->setDelayFlag('1')
    ->setDelayDay(5)
    ->setTsr($tsr);

try {
    $response = $client->send($editDelayIssue);
    $data = $response->getData();
    printResult($data);

    if ($data['RtnCode'] == 1) {
        echo "延遲開立參數已更新，請等待綠界依最新設定開立發票。\n";
    }
} catch (Exception $e) {
    echo '編輯延遲開立發票失敗：' . $e->getMessage() . PHP_EOL;
}


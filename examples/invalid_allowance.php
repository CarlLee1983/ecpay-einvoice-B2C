<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\Operations\AllowanceInvalid;

// 欲作廢的折讓單號
$allowanceNo = '2025112222177023'; 
// 原發票號碼 (必填)
$invoiceNo = 'DM20028782';
$reason = '開錯折讓';

echo "正在作廢折讓 (AllowanceNo: {$allowanceNo}, InvoiceNo: {$invoiceNo})...\n";

// 1. 初始化作廢折讓物件
$invalidAllowance = new AllowanceInvalid($merchantId, $hashKey, $hashIV);

// 2. 設定參數
$invalidAllowance->setInvoiceNo($invoiceNo)
    ->setAllowanceNo($allowanceNo)
    ->setReason($reason);

// 3. 發送請求
try {
    $response = $client->send($invalidAllowance);
    $data = $response->getData();
    printResult($data);
} catch (Exception $e) {
    echo '作廢折讓發生錯誤: ' . $e->getMessage();
}


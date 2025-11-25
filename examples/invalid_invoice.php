<?php

require __DIR__ . '/_config.php';

use CarlLee\EcPayB2C\Operations\InvalidInvoice;

// 欲作廢的發票號碼 (必須是已開立且未作廢的發票)
$invoiceNo = 'DM20028781'; 
$invoiceDate = date('Y-m-d'); // 發票日期
$reason = '訂單取消'; // 作廢原因

echo "正在作廢發票 (InvoiceNo: {$invoiceNo})...\n";

// 1. 初始化作廢物件
$invalidInvoice = new InvalidInvoice($merchantId, $hashKey, $hashIV);

// 2. 設定作廢參數
$invalidInvoice->setInvoiceNo($invoiceNo)
    ->setInvoiceDate($invoiceDate)
    ->setReason($reason);

// 3. 發送請求
try {
    $response = $client->send($invalidInvoice);
    $data = $response->getData();
    printResult($data);
} catch (Exception $e) {
    echo '作廢發票發生錯誤: ' . $e->getMessage();
}


<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\Queries\GetInvalidInvoice;

// 欲查詢的發票相關編號 (RelateNumber)
$relateNumber = 'ECPAY20251122221613592'; 
// 作廢發票號碼 (必填)
$invoiceNo = 'DM20028781';
$invoiceDate = date('Y-m-d');

echo "正在查詢作廢發票狀態 (RelateNumber: {$relateNumber}, InvoiceNo: {$invoiceNo})...\n";

// 1. 初始化查詢物件
$queryInvalid = new GetInvalidInvoice($merchantId, $hashKey, $hashIV);

// 2. 設定參數
$queryInvalid->setRelateNumber($relateNumber)
    ->setInvoiceNo($invoiceNo)
    ->setInvoiceDate($invoiceDate);

// 3. 發送請求
try {
    $response = $client->send($queryInvalid);
    $data = $response->getData();
    printResult($data);
} catch (Exception $e) {
    echo '查詢作廢發票發生錯誤: ' . $e->getMessage();
}


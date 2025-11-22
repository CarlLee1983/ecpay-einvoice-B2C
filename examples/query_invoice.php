<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\Queries\GetInvoice;

// 假設我們要查詢的發票資訊
// 注意：這裡需要填入實際存在的 RelateNumber 或 InvoiceNo (兩者擇一或皆填，視 API 需求)
// 測試時請替換為您剛剛開立成功的 RelateNumber
$relateNumber = 'ECPAY20251122221613592'; 
$invoiceNo = 'DM20028781';
$invoiceDate = date('Y-m-d'); // 發票開立日期

echo "正在查詢發票 (RelateNumber: {$relateNumber})...\n";

// 1. 初始化查詢物件
$queryInvoice = new GetInvoice($merchantId, $hashKey, $hashIV);

// 2. 設定查詢參數
$queryInvoice->setRelateNumber($relateNumber)
    ->setInvoiceDate($invoiceDate);

if (!empty($invoiceNo)) {
    $queryInvoice->setInvoiceNo($invoiceNo);
}

// 3. 發送請求
try {
    $response = $client->send($queryInvoice);
    $data = $response->getData();
    printResult($data);

    // 如果查詢成功，我們可以印出詳細資訊
    if ($data['RtnCode'] == 1 && isset($data['Data'])) {
        echo "發票明細:\n";
        // 注意：查詢結果的 Data 欄位可能是加密字串，SDK 應該會自動解密，
        // 但 GetInvoice 的實作回傳結構可能包含詳細 Items，請依實際回傳為準。
        print_r($data['Data']);
    }

} catch (Exception $e) {
    echo '查詢發票發生錯誤: ' . $e->getMessage();
}


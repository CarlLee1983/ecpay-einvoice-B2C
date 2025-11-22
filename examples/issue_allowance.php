<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\AllowanceInvoice;
use ecPay\eInvoice\Parameter\TaxType;

// 原發票號碼 (需已開立)
$invoiceNo = 'DM20028782';
$invoiceDate = date('Y-m-d');
$allowanceAmount = 100; // 折讓金額 (含稅)

echo "正在開立折讓 (InvoiceNo: {$invoiceNo})...\n";

// 1. 初始化折讓物件
$allowance = new AllowanceInvoice($merchantId, $hashKey, $hashIV);

// 2. 設定參數
$allowance->setInvoiceNo($invoiceNo)
    ->setInvoiceDate($invoiceDate)
    ->setAllowanceNotify('E') // 通知方式: E-Email, S-SMS, A-All, N-None
    ->setCustomerName('測試客戶') // 客戶名稱 (必填)
    ->setNotifyMail('test@example.com') // 若通知方式含 Email 則必填
    ->setAllowanceAmount($allowanceAmount)
    ->setItems([
        [
            'name' => '折讓商品A',
            'quantity' => 1,
            'unit' => '個',
            'price' => 100,
            'amount' => 100,
        ]
    ]);

// 3. 發送請求
try {
    $response = $client->send($allowance);
    $data = $response->getData();
    printResult($data);
    
    if (isset($data['IA_Allow_No'])) {
        echo "折讓單號: " . $data['IA_Allow_No'] . "\n";
    }
} catch (Exception $e) {
    echo '開立折讓發生錯誤: ' . $e->getMessage();
}


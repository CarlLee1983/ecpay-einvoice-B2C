<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\CheckBarcode;

// 欲驗證的手機條碼 (測試用條碼：/YC+RROR)
$barcode = '/YC+RROR';

echo "正在驗證手機條碼 (Barcode: {$barcode})...\n";

// 1. 初始化驗證物件
$checkBarcode = new CheckBarcode($merchantId, $hashKey, $hashIV);

// 2. 設定參數
$checkBarcode->setBarcode($barcode);

// 3. 發送請求
try {
    $response = $client->send($checkBarcode);
    $data = $response->getData();
    printResult($data);
    
    if ($data['RtnCode'] == 1 && isset($data['IsExist'])) {
        echo "驗證結果: " . ($data['IsExist'] == 'Y' ? "存在" : "不存在") . "\n";
    }
} catch (Exception $e) {
    echo '驗證手機條碼發生錯誤: ' . $e->getMessage();
}


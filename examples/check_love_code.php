<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\Queries\CheckLoveCode;

// 欲驗證的愛心碼 (例如: 9527)
$loveCode = '9527';

echo "正在驗證愛心碼 (LoveCode: {$loveCode})...\n";

// 1. 初始化驗證物件
$checkLoveCode = new CheckLoveCode($merchantId, $hashKey, $hashIV);

// 2. 設定參數
$checkLoveCode->setLoveCode($loveCode);

// 3. 發送請求
try {
    $response = $client->send($checkLoveCode);
    $data = $response->getData();
    printResult($data);
    
    if ($data['RtnCode'] == 1 && isset($data['IsExist'])) {
        echo "驗證結果: " . ($data['IsExist'] == 'Y' ? "存在" : "不存在") . "\n";
    }
} catch (Exception $e) {
    echo '驗證愛心碼發生錯誤: ' . $e->getMessage();
}


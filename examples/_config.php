<?php

use CarlLee\EcPayB2C\EcPayClient;

// 載入 autoload
require __DIR__ . '/../vendor/autoload.php';

// 設定時區
date_default_timezone_set('Asia/Taipei');

// 測試環境參數
$server = 'https://einvoice-stage.ecpay.com.tw';
$merchantId = '2000132';
$hashKey = 'ejCk326UnaZWKisg';
$hashIV = 'q9jcZX8Ib9LM8wYk';

// 初始化 Client
try {
    $client = new EcPayClient($server, $hashKey, $hashIV);
} catch (Exception $e) {
    echo 'Client 初始化失敗: ' . $e->getMessage();
    exit;
}

// 輔助函式：輸出結果
function printResult($response) {
    echo "========================================\n";
    echo "回應代碼 (RtnCode): " . $response['RtnCode'] . "\n";
    echo "回應訊息 (RtnMsg): " . $response['RtnMsg'] . "\n";
    
    if (isset($response['InvoiceNo'])) {
        echo "發票號碼: " . $response['InvoiceNo'] . "\n";
    }
    
    if ($response['RtnCode'] != 1) {
        echo "完整回應: " . json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    }
    echo "========================================\n\n";
}


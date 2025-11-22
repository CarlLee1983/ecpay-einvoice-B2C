<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\Queries\GetCompanyNameByTaxID;

// 欲查詢的統一編號 (例如：97025978)
$taxId = '97025978';

echo "正在查詢統一編號 ({$taxId}) 的公司名稱...\n";

// 1. 初始化查詢物件
$query = new GetCompanyNameByTaxID($merchantId, $hashKey, $hashIV);

// 2. 設定統一編號
$query->setUnifiedBusinessNo($taxId);

// 3. 發送請求
try {
    $response = $client->send($query);
    $data = $response->getData();
    printResult($data);

    if ($data['RtnCode'] == 1) {
        $companyName = $data['CompanyName'] ?? '查無公司名稱';
        echo "查詢結果：{$companyName}\n";
    }
} catch (Exception $e) {
    echo '查詢統一編號發生錯誤：' . $e->getMessage();
}


<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\Queries\GetIssueList;

$beginDate = '2024-01-01';
$endDate = '2024-01-31';

echo "查詢 {$beginDate} 至 {$endDate} 的發票清單...\n";

$query = new GetIssueList($merchantId, $hashKey, $hashIV);
$query->setBeginDate($beginDate)
    ->setEndDate($endDate)
    ->setNumPerPage(100)
    ->setShowingPage(1)
    ->setFormat('1'); // 1: JSON, 2: CSV

try {
    $response = $client->send($query);
    $data = $response->getData();
    printResult($data);

    if (isset($data['TotalCount'])) {
        echo "總筆數：{$data['TotalCount']}\n";
    }
} catch (Exception $e) {
    echo '查詢多筆發票失敗：' . $e->getMessage() . PHP_EOL;
}


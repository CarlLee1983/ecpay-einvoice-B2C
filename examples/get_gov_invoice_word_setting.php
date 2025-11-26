<?php

require __DIR__ . '/_config.php';

use CarlLee\EcPayB2C\Queries\GetGovInvoiceWordSetting;

$rocYear = date('Y') - 1911;

echo "查詢民國 {$rocYear} 年財政部字軌配號結果...\n";

$query = new GetGovInvoiceWordSetting($merchantId, $hashKey, $hashIV);
$query->setInvoiceYear((int) date('Y')); // 允許輸入西元年，類別會自動轉換為民國年

try {
    $response = $client->send($query);
    $data = $response->getData();
    printResult($data);

    if (isset($data['InvoiceInfo']) && is_array($data['InvoiceInfo'])) {
        foreach ($data['InvoiceInfo'] as $info) {
            echo sprintf(
                "期別:%d 字軌:%s 範圍:%s-%s 本數:%d\n",
                $info['InvoiceTerm'],
                $info['InvoiceHeader'],
                $info['InvoiceStart'],
                $info['InvoiceEnd'],
                $info['Number']
            );
        }
    }
} catch (Exception $e) {
    echo '查詢財政部配號結果失敗：' . $e->getMessage() . PHP_EOL;
}

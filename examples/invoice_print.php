<?php

require __DIR__ . '/_config.php';

use CarlLee\EcPayB2C\Operations\InvoicePrint;

$invoiceNo = 'UV11100016';
$invoiceDate = '2024-01-10';

echo "取得發票列印連結 (InvoiceNo: {$invoiceNo})...\n";

$print = new InvoicePrint($merchantId, $hashKey, $hashIV);
$print->setInvoiceNo($invoiceNo)
    ->setInvoiceDate($invoiceDate)
    ->setPrintStyle(1) // 1: 單面
    ->setReprint(true)
    ->setShowingDetail(1);

try {
    $response = $client->send($print);
    $data = $response->getData();
    printResult($data);

    if (isset($data['InvoiceHtml'])) {
        echo "列印網址：{$data['InvoiceHtml']}\n";
    }
} catch (Exception $e) {
    echo '取得列印連結失敗：' . $e->getMessage() . PHP_EOL;
}

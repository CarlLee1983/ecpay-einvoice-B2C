<?php

require __DIR__ . '/_config.php';

use CarlLee\EcPayB2C\Queries\GetAllowanceInvalid;

// 請改為實際要查詢的發票號碼與折讓編號
$invoiceNo = 'UV11100016';
$allowanceNo = '2019091719477262';

echo "查詢作廢折讓明細 (InvoiceNo: {$invoiceNo}, AllowanceNo: {$allowanceNo})...\n";

$query = new GetAllowanceInvalid($merchantId, $hashKey, $hashIV);
$query->setInvoiceNo($invoiceNo)
    ->setAllowanceNo($allowanceNo);

try {
    $response = $client->send($query);
    $data = $response->getData();
    printResult($data);
} catch (Exception $e) {
    echo '查詢作廢折讓失敗：' . $e->getMessage() . PHP_EOL;
}

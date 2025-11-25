<?php

require __DIR__ . '/_config.php';

use CarlLee\EcPayB2C\Operations\AllowanceInvalidByCollegiate;

$invoiceNo = 'UV11100016';
$allowanceNo = '2019091719477262';

echo "取消線上折讓 (InvoiceNo: {$invoiceNo}, AllowanceNo: {$allowanceNo})...\n";

$cancel = new AllowanceInvalidByCollegiate($merchantId, $hashKey, $hashIV);
$cancel->setInvoiceNo($invoiceNo)
    ->setAllowanceNo($allowanceNo)
    ->setReason('買家未同意');

try {
    $response = $client->send($cancel);
    $data = $response->getData();
    printResult($data);

    if ($data['RtnCode'] == 1) {
        echo "線上折讓已取消。\n";
    }
} catch (Exception $e) {
    echo '取消線上折讓失敗：' . $e->getMessage() . PHP_EOL;
}


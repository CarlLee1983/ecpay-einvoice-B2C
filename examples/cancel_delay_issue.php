<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\Operations\CancelDelayIssue;

$tsr = '請填入欲取消之延遲開立 TSR';

echo "嘗試取消延遲開立發票 (TSR: {$tsr})...\n";

$cancel = new CancelDelayIssue($merchantId, $hashKey, $hashIV);
$cancel->setTsr($tsr);

try {
    $response = $client->send($cancel);
    $data = $response->getData();
    printResult($data);

    if ($data['RtnCode'] == 1) {
        echo "延遲開立已取消。\n";
    }
} catch (Exception $e) {
    echo '取消延遲開立失敗：' . $e->getMessage() . PHP_EOL;
}


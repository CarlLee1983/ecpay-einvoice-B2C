<?php

require __DIR__ . '/_config.php';

use CarlLee\EcPayB2C\Operations\TriggerIssue;

$tsr = '請填入延遲開立回傳的TSR';

echo "準備觸發開立發票 (TSR: {$tsr})...\n";

$triggerIssue = new TriggerIssue($merchantId, $hashKey, $hashIV);

$triggerIssue->setTsr($tsr)
    ->setPayType('2');

try {
    $response = $client->send($triggerIssue);
    $data = $response->getData();
    printResult($data);

    if ($data['RtnCode'] == 4000003) {
        echo "延後開立成功，等待預約時間自動開立。\n";
    } elseif ($data['RtnCode'] == 4000004) {
        echo "立即開立成功，發票已送財政部。\n";
    }
} catch (Exception $e) {
    echo '觸發開立發票失敗：' . $e->getMessage() . PHP_EOL;
}

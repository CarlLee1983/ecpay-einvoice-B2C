<?php

require __DIR__ . '/_config.php';

use CarlLee\EcPayB2C\Operations\AddInvoiceWordSetting;
use CarlLee\EcPayB2C\Parameter\InvType;

$currentRocYear = date('Y') - 1911;
$currentTerm = (int) ceil(date('n') / 2);

echo "設定民國 {$currentRocYear} 年第 {$currentTerm} 期字軌...\n";

$operation = new AddInvoiceWordSetting($merchantId, $hashKey, $hashIV);
$operation
    ->setInvoiceYear((int) date('Y'))
    ->setInvoiceTerm($currentTerm)
    ->setInvType(InvType::GENERAL)
    ->setInvoiceHeader('AB')
    ->setInvoiceStart('10000000')
    ->setInvoiceEnd('10000049')
    ->setProductServiceId('SERVICE1');

try {
    $response = $client->send($operation);
    $data = $response->getData();
    printResult($data);

    if (isset($data['TrackID'])) {
        echo "綠界 TrackID：{$data['TrackID']}\n";
    }
} catch (Exception $e) {
    echo '設定字軌失敗：' . $e->getMessage() . PHP_EOL;
}


<?php

require __DIR__ . '/_config.php';

use CarlLee\EcPayB2C\Operations\UpdateInvoiceWordStatus;

$operation = new UpdateInvoiceWordStatus($merchantId, $hashKey, $hashIV);
$operation
    ->setTrackID('1234567890') // 請替換為 add_invoice_word_setting 回傳的 TrackID
    ->setInvoiceStatus(2); // 0:停用、1:暫停、2:啟用

try {
    $response = $client->send($operation);
    $data = $response->getData();
    printResult($data);
} catch (Exception $e) {
    echo '更新字軌狀態失敗：' . $e->getMessage() . PHP_EOL;
}

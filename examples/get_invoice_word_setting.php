<?php

require __DIR__ . '/_config.php';

use CarlLee\EcPayB2C\Parameter\InvType;
use CarlLee\EcPayB2C\Queries\GetInvoiceWordSetting;

$query = new GetInvoiceWordSetting($merchantId, $hashKey, $hashIV);
$query
    ->setInvoiceYear((int) date('Y'))
    ->setInvoiceTerm(0)   // 0: 全部期別
    ->setUseStatus(0)     // 0: 全部狀態
    ->setInvType(InvType::GENERAL->value)
    ->setInvoiceCategory(1);

try {
    $response = $client->send($query);
    $data = $response->getData();
    printResult($data);

    if (isset($data['InvoiceInfo']) && is_array($data['InvoiceInfo'])) {
        foreach ($data['InvoiceInfo'] as $info) {
            echo sprintf(
                "TrackID:%s 字軌:%s(%s) 範圍:%s-%s 狀態:%d\n",
                $info['TrackID'] ?? '',
                $info['InvoiceHeader'] ?? '',
                $info['InvType'] ?? '',
                $info['InvoiceStart'] ?? '',
                $info['InvoiceEnd'] ?? '',
                $info['UseStatus'] ?? -1
            );
        }
    }
} catch (Exception $e) {
    echo '查詢字軌失敗：' . $e->getMessage() . PHP_EOL;
}

<?php

require __DIR__ . '/_config.php';

use CarlLee\EcPayB2C\Queries\GetAllowanceList;

// 範例：以折讓單號查詢
$searchType = '0';
$allowanceNo = '2019091719477262';

echo "查詢折讓明細 (SearchType={$searchType})...\n";

$query = new GetAllowanceList($merchantId, $hashKey, $hashIV);
$query->setSearchType($searchType)
    ->setAllowanceNo($allowanceNo);

try {
    $response = $client->send($query);
    $data = $response->getData();
    printResult($data);
} catch (Exception $e) {
    echo '查詢折讓明細失敗：' . $e->getMessage() . PHP_EOL;
}

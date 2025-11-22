<?php

require __DIR__ . '/_config.php';

use ecPay\eInvoice\Operations\Invoice;
use ecPay\eInvoice\Parameter\CarrierType;
use ecPay\eInvoice\Parameter\Donation;
use ecPay\eInvoice\Parameter\PrintMark;
use ecPay\eInvoice\Parameter\TaxType;

// 1. 初始化發票物件
$invoice = new Invoice($merchantId, $hashKey, $hashIV);

// 2. 設定發票基本資訊
$relateNumber = 'ECPAY' . date('YmdHis') . rand(100, 999); // 商家自訂編號 (唯一)

echo "正在開立發票 (RelateNumber: {$relateNumber})...\n";

$invoice->setRelateNumber($relateNumber)
    ->setCustomerEmail('test@example.com') // 客戶 Email (若有設定則會收到通知)
    ->setCustomerPhone('0912345678')       // 客戶電話
    ->setPrintMark(PrintMark::NO)          // 是否列印 (NO: 不列印, YES: 列印)
    ->setDonation(Donation::NO)            // 是否捐贈 (NO: 不捐贈, YES: 捐贈)
    ->setTaxType(TaxType::DUTIABLE)        // 課稅類別 (DUTIABLE: 應稅)
    ->setSalesAmount(100)                  // 發票總金額
    ->setItems([                           // 設定商品項目
        [
            'name' => '測試商品A',
            'quantity' => 1,
            'unit' => '個',
            'price' => 100,
            'totalPrice' => 100,
        ],
    ]);

// 3. 發送請求
try {
    $response = $client->send($invoice);
    $data = $response->getData();
    printResult($data);
} catch (Exception $e) {
    echo '開立發票發生錯誤: ' . $e->getMessage();
}

// ---------------------------------------------------------
// 範例 2: 開立手機條碼載具發票
// ---------------------------------------------------------
echo "正在開立手機條碼載具發票...\n";
$relateNumber2 = 'ECPAY' . date('YmdHis') . rand(100, 999);
$invoice2 = new Invoice($merchantId, $hashKey, $hashIV);

$invoice2->setRelateNumber($relateNumber2)
    ->setCustomerEmail('test@example.com')
    ->setPrintMark(PrintMark::NO)
    ->setDonation(Donation::NO)
    ->setCarrierType(CarrierType::CELLPHONE) // 設定載具類別為手機條碼
    ->setCarrierNum('/3.14159')              // 手機條碼 (需為真實格式，此為測試用)
    ->setSalesAmount(200)
    ->setItems([
        [
            'name' => '載具測試商品',
            'quantity' => 2,
            'unit' => '組',
            'price' => 100,
            'totalPrice' => 200,
        ],
    ]);

try {
    $response = $client->send($invoice2);
    $data = $response->getData();
    printResult($data);
} catch (Exception $e) {
    echo '開立載具發票發生錯誤: ' . $e->getMessage();
}

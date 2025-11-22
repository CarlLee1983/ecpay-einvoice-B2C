# 綠界電子發票 api package

系統作業流程參閱專案內文件

## 參數
* Server: 介接網址
* MerchantID: 特約店代碼
* HashKey
* HashIV

## 完整範例程式碼

更多詳細且可直接執行的範例程式碼，請參考 `examples/` 目錄：

*   **[開立發票 (issue_invoice.php)](examples/issue_invoice.php)**：包含一般發票、載具發票等設定。
*   **[查詢發票 (query_invoice.php)](examples/query_invoice.php)**：查詢發票明細。
*   **[作廢發票 (invalid_invoice.php)](examples/invalid_invoice.php)**：將已開立發票作廢。
*   **[查詢作廢發票 (query_invalid_invoice.php)](examples/query_invalid_invoice.php)**：查詢作廢發票狀態。
*   **[開立折讓 (issue_allowance.php)](examples/issue_allowance.php)**：開立折讓證明單。
*   **[作廢折讓 (invalid_allowance.php)](examples/invalid_allowance.php)**：作廢折讓證明單。
*   **[驗證手機條碼 (check_mobile_barcode.php)](examples/check_mobile_barcode.php)**：檢查手機條碼是否有效。
*   **[驗證愛心碼 (check_love_code.php)](examples/check_love_code.php)**：檢查愛心碼是否有效。

## 快速入門範例

### 開立發票

```php
$server = 'https://einvoice-stage.ecpay.com.tw';
$id = '2000132';
$key = 'ejCk326UnaZWKisg';
$iv = 'q9jcZX8Ib9LM8wYk';

// 初始化 Client
$client = new ecPay\eInvoice\EcPayClient($server, $key, $iv);

// 初始化 Invoice
$invoice = new ecPay\eInvoice\Invoice($id, $key, $iv);

$invoice->setRelateNumber('YEP' . date('YmdHis'))
    ->setCustomerEmail('cylee@chyp.com.tw')
    ->setItems([
        [
            'name' => '商品範例',
            'quantity' => 1,
            'unit' => '個',
            'price' => 100,
            'totalPrice' => 100,
        ],
    ])
    ->setSalesAmount(100);

// 發送請求
$response = $client->send($invoice);
$data = $response->getData();
```

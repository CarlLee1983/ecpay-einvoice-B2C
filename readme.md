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
*   **[設定字軌與配號 (add_invoice_word_setting.php)](examples/add_invoice_word_setting.php)**：新增當期字軌區間。
*   **[設定字軌狀態 (update_invoice_word_status.php)](examples/update_invoice_word_status.php)**：啟用/暫停既有字軌。
*   **[查詢字軌 (get_invoice_word_setting.php)](examples/get_invoice_word_setting.php)**：檢視字軌使用狀態與 TrackID。
*   **[查詢財政部配號結果 (get_gov_invoice_word_setting.php)](examples/get_gov_invoice_word_setting.php)**：查詢財政部授權綠界的字軌配號。

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
$invoice = new ecPay\eInvoice\Operations\Invoice($id, $key, $iv);

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

## 模組分群

- `ecPay\eInvoice\Operations\*`：包含開立發票、作廢與折讓等一般發票作業類別（例如 `Invoice`, `InvalidInvoice`, `AllowanceInvoice`）。
- `ecPay\eInvoice\Queries\*`：封裝查詢與驗證類別（例如 `GetInvoice`, `CheckBarcode`），與作業模組解耦。
- `ecPay\eInvoice\Notifications\*`：目前提供 `InvoiceNotify`，用於推播發票、折讓或中獎通知。
- `ecPay\eInvoice\Printing\*`：列印功能尚未實作，`README` 僅為占位，未來若新增列印 API 會在此擴充。

> 以上模組皆繼承共同的 `Content` 基底類別，仍可透過相同的 `EcPayClient` 傳送請求。

## 工廠模式與 Laravel 整合

### 純 PHP 工廠

`OperationFactory` 可依別名快速建立 `Operations\*`、`Queries\*` 等物件並注入共用憑證。別名預設對應為：

- `invoice` → `Operations\Invoice`
- `operations.invalid_invoice` → `Operations\InvalidInvoice`
- `queries.get_invoice` → `Queries\GetInvoice`

範例：

```php
use ecPay\eInvoice\EcPayClient;
use ecPay\eInvoice\Factories\OperationFactory;

$factory = new OperationFactory([
    'merchant_id' => $merchantId,
    'hash_key' => $hashKey,
    'hash_iv' => $hashIV,
]);

$invoice = $factory->make('invoice')
    ->setRelateNumber('YEP' . date('YmdHis'))
    ->setSalesAmount(100)
    ->setItems([
        ['name' => '測試商品', 'quantity' => 1, 'unit' => '組', 'price' => 100],
    ]);

$client = new EcPayClient($server, $hashKey, $hashIV);
$data = $client->send($invoice)->getData();
```

如需自訂別名或預設欄位，可呼叫 `alias()`、`addInitializer()`：

```php
use ecPay\eInvoice\Content;

$factory->alias('operations.mobile_invoice', \App\Invoices\MobileInvoice::class);
$factory->addInitializer(function (Content $content) {
    $content->setRelateNumber('APP' . date('YmdHis'));
});
```

### Laravel Service Container + Facade

- 套件已支援 auto-discovery，或可手動在 `config/app.php` 註冊 `ecPay\eInvoice\Laravel\EcPayServiceProvider::class`。
- 發布設定檔：`php artisan vendor:publish --tag=ecpay-einvoice-config`
- 設定檔 `config/ecpay-einvoice.php` 內可調整 MerchantID、別名綁定與初始化器。

使用範例：

```php
use ecPay\eInvoice\Laravel\Facades\EcPayInvoice;
use ecPay\eInvoice\Laravel\Facades\EcPayQuery;

$invoice = EcPayInvoice::make()
    ->setRelateNumber('YEP' . now()->format('YmdHis'))
    ->setSalesAmount(100)
    ->setItems([
        ['name' => 'Laravel Facade', 'quantity' => 1, 'unit' => '式', 'price' => 100],
    ]);

$response = app('ecpay.client')->send($invoice)->getData();

$query = EcPayQuery::invoice()->setInvoiceNumber('AB12345678');
$result = app('ecpay.client')->send($query)->getData();
```

同時也可直接透過容器解析 `app('ecpay.allowance')`、`app(OperationFactoryInterface::class)` 等實例，自行調整後送出請求。

## 文件資源

- `docs/README.md`：文件入口，整理常用章節索引與官方最新下載連結。
- `docs/api-overview.md`：快速瀏覽介接流程、模組與共用欄位。
- `docs/error-codes.md`：常見錯誤碼與程式內部驗證訊息參考。
- `docs/README.md#使用流程圖--flowcharts`：前置設定、開立發票、折讓/作廢/註銷等 Mermaid 流程圖。
- 官方 PDF：<https://developers.ecpay.com.tw/?p=7809>

---

# ECPay e-Invoice API Package (English Overview)

This library wraps the official ECPay e-Invoice API. Use it to create, void, and query invoices/allowances, manage tracks, and trigger notifications with consistent encryption and validation helpers.

## Parameters
- Server: API endpoint (stage or production)
- MerchantID: merchant code registered with ECPay
- HashKey / HashIV: AES credentials for encrypting `Data`

## Sample Scripts
Key runnable samples live under `examples/`. Common ones include:
- **issue_invoice.php** – create regular invoices (with carrier / donation settings)
- **query_invoice.php** – fetch invoice details
- **invalid_invoice.php / query_invalid_invoice.php** – void invoices and confirm status
- **issue_allowance.php / invalid_allowance.php** – manage allowances
- **check_mobile_barcode.php / check_love_code.php** – validate carrier and donation codes
- **add_invoice_word_setting.php / update_invoice_word_status.php / get_invoice_word_setting.php** – allocate, enable/disable, and inspect invoice tracks
- **get_gov_invoice_word_setting.php** – retrieve Ministry of Finance track allocation

## Quick Start
```php
$client = new ecPay\eInvoice\EcPayClient($server, $hashKey, $hashIV);
$invoice = new ecPay\eInvoice\Operations\Invoice($merchantId, $hashKey, $hashIV);

$invoice->setRelateNumber('YEP' . date('YmdHis'))
    ->setCustomerEmail('demo@example.com')
    ->setItems([
        ['name' => 'Demo Item', 'quantity' => 1, 'unit' => 'pcs', 'price' => 100, 'totalPrice' => 100],
    ])
    ->setSalesAmount(100);

$response = $client->send($invoice);
$data = $response->getData();
```

## Module Groups
- `Operations\*`: create/void invoices and allowances (`Invoice`, `InvalidInvoice`, `AllowanceInvoice`, etc.)
- `Queries\*`: lookup invoice/allowance status or validate carriers (`GetInvoice`, `CheckBarcode`, …)
- `Notifications\*`: push invoice/allowance/winning notifications (`InvoiceNotify`)
- `Printing\*`: reserved for future printing helpers

All modules extend `Content`, so you can share the same `EcPayClient` to send requests.

## Documentation Resources
- `docs/README.md`: entry point for local docs
- `docs/api-overview.md`: flow overview, module list, shared parameters
- `docs/error-codes.md`: maps common API errors to in-project validation rules
- Official PDF: <https://www.ecpay.com.tw/Content/files/ecpay_einvoice_v3_0_0.pdf>

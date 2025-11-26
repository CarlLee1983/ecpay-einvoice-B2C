## Laravel 協調器使用範例

以下程式片段假設執行於 Laravel 專案中，並已在 `config/app.php` 或 auto-discovery 中載入 `EcPayServiceProvider`。

```php
use CarlLee\EcPayB2C\DTO\InvoiceItemDto;
use CarlLee\EcPayB2C\Laravel\Facades\EcPayInvoice;
use CarlLee\EcPayB2C\Laravel\Facades\EcPayQuery;

// 1) 開立發票
$response = EcPayInvoice::issue(function ($invoice) {
    $invoice->setRelateNumber('INV' . now()->format('YmdHis'))
        ->setCustomerEmail('demo@example.com')
        ->setItems([
            InvoiceItemDto::fromArray([
                'name' => '商品Ａ',
                'quantity' => 1,
                'unit' => '組',
                'price' => 500,
            ]),
        ]);
});

// 2) 查詢發票
$queryResponse = EcPayQuery::coordinate('get_invoice', function ($query) {
    $query->setRelateNumber('INV20240101000001');
});
```

協調器會自動從 `OperationFactory` 取得對應 operation，執行回呼設定，再交給 `EcPayClient` 發送並回傳 `Response` 實例。只要專案已正確設定 `config/ecpay-einvoice.php`，即可在任意呼叫點統一使用上述流程。***


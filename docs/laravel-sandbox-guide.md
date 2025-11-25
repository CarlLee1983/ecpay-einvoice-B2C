# Laravel 沙盒測試指引

下列流程可在全新 Laravel 專案（或 Orchestra Testbench skeleton）中驗證本套件是否可於 sandbox 環境正確整合運作。示範語系採繁體中文，依需求可調整。

## 1. 建立測試專案

### 1.1 直接建立 Laravel 專案

```bash
laravel new ecpay-einvoice-sandbox
cd ecpay-einvoice-sandbox
cp .env.example .env
php artisan key:generate
```

### 1.2 使用 Orchestra Testbench

適合套件開發者以最小環境測試：

```bash
composer create-project orchestra/testbench sandbox-laravel
cd sandbox-laravel
cp .env.example .env
```

> 提示：建議為沙盒專案建立獨立 git repo 或使用臨時資料夾，避免與實際服務混用。

## 2. 安裝套件與設定

1. 安裝本套件：
   ```bash
   composer require ecpay/laravel-einvoice
   ```
2. （若未啟用 auto-discovery）手動在 `config/app.php` 新增 Service Provider：
   ```php
   'providers' => [
       // ...
       ecPay\eInvoice\Laravel\EcPayServiceProvider::class,
   ],
   ```
3. 發布設定檔並填入 sandbox 憑證／伺服器：
   ```bash
   php artisan vendor:publish --tag=ecpay-einvoice-config
   ```
4. 編輯 `config/ecpay-einvoice.php`，填入官方測試憑證或 Mock：
   ```php
   'server' => env('ECPAY_EINVOICE_SERVER', 'https://einvoice-stage.ecpay.com.tw'),
   'merchant_id' => env('ECPAY_EINVOICE_MERCHANT_ID', '2000132'),
   'hash_key' => env('ECPAY_EINVOICE_HASH_KEY', 'StageHashKey'),
   'hash_iv' => env('ECPAY_EINVOICE_HASH_IV', 'StageHashIV'),
   ```
5. `.env` 補上對應的 `ECPAY_EINVOICE_*` 變數，確保開發與 sandbox 分離。

## 3. 撰寫驗證流程

### 3.1 Route 驗證（GET `/ecpay/sandbox`）

在 `routes/web.php` 新增：

```php
use ecPay\eInvoice\DTO\InvoiceItemDto;
use ecPay\eInvoice\Laravel\Facades\EcPayInvoice;

Route::get('/ecpay/sandbox', function () {
    $invoice = EcPayInvoice::make()
        ->setRelateNumber('SBX' . now()->format('YmdHis'))
        ->setItems([
            InvoiceItemDto::fromArray([
                'name' => 'Sandbox 測試',
                'quantity' => 1,
                'unit' => '份',
                'price' => 50,
            ]),
        ]);

    // 以 sandbox 為例，僅回傳 payload，不實際送出
    return response()->json($invoice->getPayload());
});
```

### 3.2 Artisan 指令（`php artisan ecpay:sandbox:ping`）

建立 `app/Console/Commands/EcPaySandboxPing.php`：

```php
namespace App\Console\Commands;

use ecPay\eInvoice\Laravel\Facades\EcPayQuery;
use Illuminate\Console\Command;

class EcPaySandboxPing extends Command
{
    protected $signature = 'ecpay:sandbox:ping {invoiceNumber?}';
    protected $description = '檢查 EcPay 套件是否可在 sandbox 運作';

    public function handle(): int
    {
        $invoiceNumber = $this->argument('invoiceNumber') ?? 'AB12345678';

        $query = EcPayQuery::invoice()->setInvoiceNumber($invoiceNumber);

        $this->info('Query payload ready:');
        $this->line(json_encode($query->getPayload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->comment('若需實際送出請呼叫 app(EcPayClient::class)->send($query)');

        return Command::SUCCESS;
    }
}
```

並在 `app/Console/Kernel.php` 註冊：

```php
protected $commands = [
    \App\Console\Commands\EcPaySandboxPing::class,
];
```

### 3.3 測試程式（PHPUnit/Pest）

新增 `tests/Feature/EcPaySandboxTest.php`：

```php
use ecPay\eInvoice\Factories\OperationFactoryInterface;
use ecPay\eInvoice\Laravel\Facades\EcPayInvoice;

it('can resolve invoice operation with sandbox credentials', function () {
    $invoice = EcPayInvoice::make();

    expect($invoice->getPayload()['Data']['MerchantID'])->toBe(config('ecpay-einvoice.merchant_id'));
});

it('factory produces isolated instances', function () {
    $factory = app(OperationFactoryInterface::class);
    $invoiceA = $factory->make('invoice');
    $invoiceB = $factory->make('invoice');

    expect($invoiceA)->not->toBe($invoiceB);
});
```

## 4. 執行沙盒檢查

| 指令 | 用途 | 預期輸出 |
| --- | --- | --- |
| `php artisan config:clear` | 確保最新設定生效 | 無錯誤訊息 |
| `php artisan ecpay:sandbox:ping` | 驗證 Facade 能建立查詢 payload | 顯示 JSON payload 且結尾提示送出方式 |
| `php artisan serve` + GET `/ecpay/sandbox` | 透過瀏覽器/HTTP client 檢查 route 是否可產出 payload | 200 回應，內容為 invoice payload |
| `php artisan test --filter=EcPaySandboxTest` | 自動化確認工廠/Facade 能被解析 | 測試通過 (green) |

若需模擬實際送出，可在 sandbox 內注入 Mock `EcPayClient`（例如使用 Laravel container 或 Orchestra Testbench）並檢查 `send()` 是否收到正確的 `Content` 實例，再決定是否串接到正式 API。

---

藉由上述步驟，即可在隔離環境中快速驗證套件於 Laravel 專案的整合結果，確保佈署前行為與設定皆可預期。需進一步自動化時，也可將 Artisan 指令與測試納入 CI pipeline。 


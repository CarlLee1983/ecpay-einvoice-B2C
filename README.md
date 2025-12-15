# 綠界電子發票 API（B2C）套件

此套件封裝綠界電子發票（B2C）API，提供 Operations/Queries/Notifications 的一致介面、DTO-based 欄位建構、加密/解密與 Laravel 整合。

## 需求
- PHP `^8.3`

## 安裝
```bash
composer require carllee1983/ecpay-einvoice-b2c
```

## 快速開始
```php
use CarlLee\EcPayB2C\EcPayClient;
use CarlLee\EcPayB2C\Operations\Invoice;
use CarlLee\EcPayB2C\DTO\InvoiceItemDto;

$server = 'https://einvoice-stage.ecpay.com.tw';
$merchantId = '2000132';
$hashKey = 'ejCk326UnaZWKisg';
$hashIV = 'q9jcZX8Ib9LM8wYk';

$client = new EcPayClient($server, $hashKey, $hashIV);

$invoice = new Invoice($merchantId, $hashKey, $hashIV);
$invoice->setRelateNumber('YEP' . date('YmdHis'))
    ->setCustomerEmail('demo@example.com')
    ->setItems([
        InvoiceItemDto::fromArray(['name' => '商品範例', 'quantity' => 1, 'unit' => '個', 'price' => 100]),
    ])
    ->setSalesAmount(100);

$response = $client->send($invoice);
$data = $response->getData();
```

## 命令契約（重要）
- `EcPayClient::send()` 只接受 `CarlLee\EcPayB2C\Contracts\EncryptableCommandInterface`（通常直接繼承 `CarlLee\EcPayB2C\Content` 即可）。
- 舊的 `CarlLee\EcPayB2C\InvoiceInterface` 已標記 deprecated（相容層）。

## 文件
- `docs/README.md`：文件索引與流程圖
- `docs/api-overview.md`：介接流程與模組總覽
- `docs/error-codes.md`：常見錯誤碼與驗證訊息


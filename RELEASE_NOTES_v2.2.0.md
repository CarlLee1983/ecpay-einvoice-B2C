# Release Notes - v2.2.0

## 🎉 版本資訊

**版本號**：v2.2.0  
**發布日期**：2025-11-23  
**類型**：功能增強版本（向下兼容）

---

## ✨ 主要更新

### 1. Operation Factory 工廠層 🏗️
- 新增 `OperationFactoryInterface` 與 `OperationFactory`，統一管理 `Operations/*`、`Queries/*`、`Notifications/*` 等模組的建立流程。
- 支援自訂別名 (`alias()`)、客製 resolver (`extend()`) 與共用初始化器 (`addInitializer()`)，可輕鬆注入 MerchantID/HashKey/HashIV 或設定預設欄位，例如自動產生 `RelateNumber`。
- 工廠可搭配純 PHP 專案使用，也能被 Laravel Service Container 解析，減少重複樣板碼。

### 2. Laravel Service Container 與 Facade ⚙️
- 新增 `config/ecpay-einvoice.php`，支援 `php artisan vendor:publish --tag=ecpay-einvoice-config` 將設定檔複製到專案內。
- `ecPay\eInvoice\Laravel\EcPayServiceProvider` 會：
  - 將設定值載入並綁定 `EcPayClient` 與 `OperationFactoryInterface`。
  - 在容器中提供 `ecpay.invoice`、`ecpay.query_invoice` 等快捷解析點。
- 提供 `EcPayInvoice`、`EcPayQuery` Facade，能以 `EcPayInvoice::make()`、`EcPayQuery::invoice()` 取得常用操作類別，快速整合 Laravel 應用程式。

### 3. 文件與 Composer 調整 📚
- README 與 `docs/README.md` 新增「工廠模式」與「Laravel 整合」章節，範例涵蓋純 PHP、Service Container 與 Facade。
- `composer.json` 更新內容：
  - 加入 Laravel auto-discovery 設定。
  - 新增 dev 依賴 `orchestra/testbench`，支援 Laravel Service Provider 測試。
  - 調整作者聯絡資訊、移除 `version` 欄位，使套件符合 Packagist 發佈規範。

### 4. 測試與品質 🧪
- 新增 `test/OperationFactoryTest.php` 驗證工廠別名與初始化邏輯。
- 新增 `test/Laravel/EcPayServiceProviderTest.php`，透過 Orchestra Testbench 確認 Service Provider 綁定與 Facade 解析。
- `composer test`：共 267 個測試案例、574 個斷言全數通過，僅維持既有 1 項 risky case（`InvalidInvoiceTest::testQuickCheck` 未含斷言）。  
- `composer validate`：schema 驗證通過，僅有當前開發環境缺少 `opcache.so` / `mongodb.so` 擴充模組的警告，不影響套件發佈。

---

## 🔄 遷移指南

### 是否需要更新既有程式？
**不需要。** 本版本對既有 API 完全向下兼容，既有純 PHP 呼叫方式保持不變。

### 建議升級步驟
1. 在 Laravel 專案中執行：
   ```bash
   php artisan vendor:publish --tag=ecpay-einvoice-config
   ```
2. 於 `.env` 設定 `ECPAY_EINVOICE_SERVER`、`ECPAY_EINVOICE_MERCHANT_ID`、`ECPAY_EINVOICE_HASH_KEY`、`ECPAY_EINVOICE_HASH_IV`。
3. 透過 Facade：
   ```php
   use ecPay\eInvoice\Laravel\Facades\EcPayInvoice;

   $invoice = EcPayInvoice::make()
       ->setRelateNumber('APP' . now()->format('YmdHis'))
       ->setSalesAmount(1000)
       ->setItems([...]);

   $result = app('ecpay.client')->send($invoice)->getData();
   ```

---

## 📦 安裝 / 更新

```bash
composer require ecpay/einvoice:^2.2
# 或更新既有專案
composer update ecpay/einvoice
```

---

## 🧪 測試

```bash
# 運行所有測試
composer test

# 僅執行 Laravel Service Provider 測試
vendor/bin/phpunit test/Laravel/EcPayServiceProviderTest.php

# 僅執行工廠測試
vendor/bin/phpunit test/OperationFactoryTest.php
```

> 若本機環境缺少 `opcache.so` 或 `mongodb.so`，`composer` 指令會提示啟動警告，不影響測試與套件發佈。

---

## 📚 文件

- [README.md](readme.md) – 快速開始與範例
- [docs/README.md](docs/README.md) – 文檔索引與流程圖
- [CHANGELOG.md](CHANGELOG.md) – 完整變更記錄

---

## 🙏 致謝

感謝所有開發者持續提供回饋與建議，讓本版本得以順利整合 Laravel 與新工廠架構。

---

## 📝 完整變更清單

詳情請參閱 [CHANGELOG.md](CHANGELOG.md)。

---


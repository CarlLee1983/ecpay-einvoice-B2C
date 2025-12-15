# Release Notes - v2.3.0

## 🎉 版本資訊

**版本號**：v2.3.0  
**發布日期**：2025-11-25  
**類型**：功能增強版本（次版號）

---

## ✨ 主要更新

### 1. DTO 與命令契約全面落地 🧱
- `InvoiceItemDto`、`AllowanceItemDto`、`AllowanceCollegiateItemDto`、`ItemCollection`、`ItemDtoInterface` 取代舊有陣列，集中欄位驗證並提供 `fromArray()` 轉換。
- 新增 `RqHeaderDto`，以物件化方式管理 `RqHeader` 欄位與同步。
- （現行版本）`Contracts\EncryptableCommandInterface` 統一所有可送出的命令，`EcPayClient::send()` 僅接受此介面，並在呼叫時自動灌入 HashKey/HashIV。
- `Content` 與所有 Operation 的 `setItems()`、`validation()`、`getPayload()` 均改寫為使用 DTO 與 `PayloadEncoder`，減少重複邏輯。

### 2. 基礎設施與傳輸層重構 🔐
- 新增 `Infrastructure\CipherService` 與 `PayloadEncoder`，集中 AES 加解密與傳輸層編碼，命令可注入自訂 encoder 以支援擴充情境。
- `EcPayClient` 現在直接向命令索取 `PayloadEncoder` 與 `RequestPath`，維持單一入口而不需在命令內維護 server。
- `AES.php`、`Content.php`、`InvoiceValidator.php` 等核心類別同步調整，行為更容易被測試與覆蓋。

### 3. Laravel 協調器與 Sandbox 指引 ⚙️
- `Laravel\Services\OperationCoordinator` 封裝「工廠 → 回呼 → Client」流程，`EcPayInvoice::coordinate()` / `EcPayQuery::coordinate()` 可共用相同協調器。
- `test/Laravel/EcPayServiceProviderTest.php` 加入多商店、多伺服器重綁情境，確保 `OperationFactory` 與 `EcPayClient` 狀態彼此隔離。
- 新增 `docs/laravel-sandbox-guide.md`，教學如何以 Orchestra Testbench 或本機 Laravel Sandbox 透過 Composer path repository 安裝套件並驗證路由 / Artisan / 測試。

### 4. 文件與範例 📚
- README、examples、`docs/api-overview.md` 改為 DTO 寫法，並補充協調器、PayloadEncoder 說明。
- `examples/laravel_coordinator.md` 示範如何在 Laravel 端串接協調器與 Facade。

---

## 🧪 測試與品質
- `composer test`：共 300+ 測試案例、超過 600 斷言全數通過。
- 新增 `PayloadEncoderTest`、擴充 `ItemCollectionTest`、`Laravel/EcPayServiceProviderTest`，涵蓋 DTO 集合、Payload encode/decode、協調器與多商店狀態隔離。

---

## 🔄 遷移指南
1. **setItems 確認**：呼叫 `setItems()` 時請傳入 `InvoiceItemDto::fromArray([...])` 等 DTO，如果仍使用純陣列可先用 `fromArray()` 轉換。
2. **Laravel 協調器**：若已透過 Facade `EcPayInvoice::issue()`、`EcPayQuery::coordinate()`，不需額外修改；若自行解析 `EcPayClient`，可考慮注入 `OperationCoordinator` 以共用流程。
3. **自訂命令**：若自訂 Operation，請實作 `EncryptableCommandInterface`（通常直接繼承 `Content` 即可），並確保 `getPayloadEncoder()` 可回傳預期的 encoder，且可透過 `getContent()` 產生加密後的 `Data`。

---

## 📦 安裝 / 更新

```bash
composer require "ecpay/einvoice:^2.3"
# 或更新既有專案
composer update ecpay/einvoice
```

如尚未發佈至 Packagist，可在 sandbox 專案的 `composer.json` 加入：

```json
"repositories": [
    {
        "type": "path",
        "url": "/path/to/ecpay-einvoice-B2B",
        "options": { "symlink": false }
    }
]
```

接著執行 `composer require "ecpay/einvoice:*@dev"`（記得用引號避免 zsh 展開 `*`）。

---

## 📚 文件

- [docs/laravel-sandbox-guide.md](docs/laravel-sandbox-guide.md) – Orchestra Testbench / Laravel Sandbox 實作指南
- [README.md](README.md) – 快速開始與純 PHP + Laravel 例子
- [docs/README.md](docs/README.md) – 文件索引與流程圖
- [CHANGELOG.md](CHANGELOG.md) – 完整變更記錄

---

## 🚀 發佈流程

```bash
git checkout master
git pull origin master
composer test
git tag v2.3.0
git push origin master --tags
# 若要發佈至 Packagist，登入後點選「Update」或設定 Git webhook 自動同步
```

---

## 🙏 致謝

感謝所有使用者對多商店、多伺服器情境與 Laravel 整合的回饋，促成本次協調器、DTO 與 Sandbox 指南的完善。歡迎持續透過 issue 或 PR 分享建議。

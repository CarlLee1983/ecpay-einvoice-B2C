# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/zh-TW/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/lang/zh-TW/).

## [Unreleased]

## [4.1.0] - 2025-11-26

### Added (新增)
- **例外類別架構改善**
  - `EcPayException` - 基礎例外類別，新增 context 追蹤機制（`getContext()`, `setContext()`, `addContext()`）
  - `ValidationException` - 新增靜態工廠方法（`required()`, `invalid()`, `tooLong()`, `notInRange()`）
  - `EncryptionException` - 新增靜態工廠方法（`encryptionFailed()`, `decryptionFailed()`, `invalidKey()`）
  - `ApiException` - 新增 API 請求/回應錯誤例外類別
  - `PayloadException` - 新增 Payload 結構/資料無效例外類別
  - `ConfigurationException` - 新增靜態工廠方法（`missing()`, `invalid()`）
  - `InvalidParameterException` - 新增靜態工廠方法（已標記 deprecated）
- **Response 類別新增便利方法**
  - `isSuccess()` - `success()` 的別名
  - `isError()` - 檢查是否為錯誤回應
  - `getCode()` - 取得回應代碼（RtnCode）
  - `toArray()` - 轉換為陣列
  - `throw()` - 若為錯誤則拋出 `ApiException`
- **Request 類別改善**
  - `MIN_TLS_VERSION` 常數 - 最低 TLS 版本設定
  - `setVerifySsl()` 靜態方法 - SSL 驗證開關
  - `createDefaultClient()` 方法 - 分離 HTTP Client 建立邏輯
  - 新增 timeout 和 connect_timeout 設定
- **config 新增 `verify_ssl` 設定選項**
- **EcPayServiceProvider 新增 `configureRequest()` 方法**

### Changed (變更)
- **統一 PHP 8.3 現代語法**
  - `Content` 和 `EcPayClient` 使用 typed properties
  - `CipherService` 使用新的例外靜態工廠方法
  - `PayloadEncoder` 使用 `json_validate()` (PHP 8.3) 與新例外類別
- **Request 類別改用 `ApiException` 處理錯誤**

### Internal (內部改進)
- 與 B2B 專案架構同步，提升程式碼一致性
- 核心類別統一使用自訂例外的靜態工廠方法

## [4.0.1] - 2025-11-26

### Removed (移除)
- 移除 `src/AES.php` trait（已由 `Infrastructure\CipherService` 取代）
- 移除 `RqHeaderDto::toArray()` 方法（已由 `toPayload()` 取代）

### Changed (變更)
- 新增 `test/Unit/CipherServiceTest.php` 測試 `CipherService` 加解密功能

## [4.0.0] - 2025-11-26

### BREAKING CHANGES (破壞性變更)
- **Parameter Enum 改造**：所有 `src/Parameter/` 類別從常數類別改為 PHP 8.1 String-backed Enum
  - 受影響類別：`AllowanceNotifyType`, `CarrierType`, `ClearanceMark`, `Donation`, `InvoiceTagType`, `InvType`, `NotifiedType`, `NotifyType`, `PrintMark`, `SpecialTaxType`, `TaxType`, `VatType`
  - 使用方式變更：`TaxType::DUTIABLE` → `TaxType::DUTIABLE->value`

### Added (新增)
- PHP 8.3 Typed Class Constants：所有常數加上類型宣告（`const int`, `const string`）
- PHP 8.3 `#[\Override]` 屬性：標記所有實作介面的方法
- PHP 8.2 Readonly Classes：`InvoiceItemDto`, `AllowanceItemDto`, `AllowanceCollegiateItemDto` 改為 `readonly class`
- 所有 Parameter 類別加入 `declare(strict_types=1)`
- 每個 Enum 類別加入中文 DocBlock 說明

### Changed (變更)
- `CipherService`：使用 constructor property promotion
- `PayloadEncoder`：使用 constructor property promotion
- `OperationCoordinator`：使用 constructor property promotion
- `InvoiceValidator`：調整程式碼以適應 Enum 變更
- 所有 Operations/Queries/Notifications 中的常數引用更新為 Enum 語法

### Tests (測試)
- 更新 5 個測試檔案以適應 Enum 變更
- 更新 3 個範例檔案

### Migration Guide (升級指南)
1. 搜尋並替換所有 `Parameter::CONSTANT` 為 `Parameter::CONSTANT->value`
2. 或使用 Enum 的原生方法（如 `TaxType::tryFrom()`）進行值解析
3. 執行 `composer test` 確認所有測試通過

## [3.0.0] - 2025-11-25

### BREAKING CHANGES (破壞性變更)
- **Namespace 重構**：將所有命名空間從 `ecPay\eInvoice\*` 更改為 `CarlLee\EcPayB2C\*`
  - 所有 `use ecPay\eInvoice\...` 語句需更改為 `use CarlLee\EcPayB2C\...`
  - Laravel Service Provider 變更為 `CarlLee\EcPayB2C\Laravel\EcPayServiceProvider`
  - Facade 變更為 `CarlLee\EcPayB2C\Laravel\Facades\EcPayInvoice` 與 `EcPayQuery`
  - composer.json autoload 命名空間已同步更新

### Changed (變更)
- 更新 `composer.json` 中的 PSR-4 autoload 配置
- 更新 `docs/README.md` 與 `docs/laravel-sandbox-guide.md` 中的 namespace 引用
- 更新 `readme.md` 中的使用範例
- 更新 `examples/` 目錄中所有範例檔案的 namespace 引用
- 更新 `OperationFactory::BASE_NAMESPACE` 常量

### Migration Guide (升級指南)
1. 搜尋並替換所有 `ecPay\eInvoice` 為 `CarlLee\EcPayB2C`
2. 執行 `composer dump-autoload` 重新生成 autoload
3. 若使用 Laravel，設定檔無需更改（auto-discovery 會自動載入新的 Service Provider）

## [2.3.0] - 2025-11-25

### Added (新增)
- `src/Contracts/CommandInterface.php`：統一 `EcPayClient` 與所有命令物件的介面契約
- `src/Infrastructure/CipherService.php` 與 `PayloadEncoder.php`：抽離加解密與傳輸層，提供命令可重複使用的編碼服務
- 全新 DTO 與集合：
  - `DTO/InvoiceItemDto.php`、`AllowanceItemDto.php`、`AllowanceCollegiateItemDto.php`、`ItemCollection.php`、`ItemDtoInterface.php`
  - `DTO/RqHeaderDto.php` 取代舊陣列欄位並集中驗證邏輯
- `Laravel/Services/OperationCoordinator.php`：協調工廠、回呼與 Client，提供 Facade 與應用程式單一入口
- `docs/laravel-sandbox-guide.md`：示範如何在 Orchestra Testbench / Laravel sandbox 以本機路徑安裝並驗證套件
- 新增 `test/PayloadEncoderTest.php`、擴充 `test/Laravel/EcPayServiceProviderTest.php` 覆蓋多商店／多 server 情境

### Changed (變更)
- `EcPayClient::send()` 現在僅接受 `CommandInterface`，並於送出前同步 HashKey/HashIV、統一 Payload encode/decode 流程
- `Content` 及各 Operation 皆改用 DTO 生成 Items 與 RqHeader；`setItems()` 支援 DTO 或陣列輸入並自動轉換
- `Laravel\EcPayServiceProvider` 綁定新的 OperationCoordinator，Facade (`EcPayInvoice`/`EcPayQuery`) 可直接透過協調器送出作業
- README、examples、docs 調整為 DTO 寫法並加入協調器說明與 sandbox 測試指南

### Tests (測試)
- 新增多個與 DTO、PayloadEncoder、OperationCoordinator 相關的單元與整合測試，覆蓋多商店/多伺服器重綁、工廠別名與 Payload 編碼流程

## [2.2.0] - 2025-11-23

### Added (新增)
- `src/Factories/OperationFactoryInterface.php` 與 `OperationFactory.php`：統一管理操作/查詢/通知類別建立、別名對應、自訂初始化流程
- Laravel 整合套件化：`config/ecpay-einvoice.php`、`Laravel/EcPayServiceProvider.php`、`Facades/EcPayInvoice.php`、`Facades/EcPayQuery.php`，支援 Service Container 綁定、Facade 與 `vendor:publish`
- 新增 `orchestra/testbench` 依賴以及 `test/OperationFactoryTest.php`、`test/Laravel/EcPayServiceProviderTest.php`，確保工廠與 Service Provider/Facade 綁定可被測試

### Changed (變更)
- README 與 `docs/README.md` 新增「工廠模式與 Laravel 整合」章節，提供純 PHP、Service Container 與 Facade 的使用範例與設定指引
- `composer.json` 新增 Laravel auto-discovery 設定、dev 依賴 `orchestra/testbench`，並調整作者信箱、移除 `version` 欄位以符合 Packagist 規範
- `composer.lock` 重新產生以反映新的依賴集

### Tests (測試)
- `composer test`：全部 267 個測試案例（574 個 assertion）皆通過

## [2.1.0] - 2024-11-22

### Added (新增)
- 於 `src/Printing/README.md` 建立列印模組占位說明，預留未來列印 API 擴充空間
- 新增 `docs/` 目錄（`README.md`, `api-overview.md`, `error-codes.md`），整理官方 PDF 的常用資訊並提供快速連結
- 新增 8 個完整的使用範例檔案（`examples/` 目錄）
  - `issue_invoice.php` - 開立發票範例
  - `issue_allowance.php` - 開立折讓範例
  - `invalid_invoice.php` - 作廢發票範例
  - `invalid_allowance.php` - 作廢折讓範例
  - `query_invoice.php` - 查詢發票範例
  - `query_invalid_invoice.php` - 查詢作廢發票範例
  - `check_love_code.php` - 檢查愛心碼範例
  - `check_mobile_barcode.php` - 檢查手機條碼範例
  - `_config.php` - 範例設定檔
- 新增 9 個完整的單元測試檔案（174 個測試案例，408 個斷言）
  - `InvoiceValidatorTest.php` - 驗證邏輯測試（19 個測試）
  - `AllowanceInvoiceTest.php` - 折讓功能測試（25 個測試）
  - `ResponseTest.php` - 回應處理測試（20 個測試）
  - `AllowanceInvalidTest.php` - 作廢折讓測試（15 個測試）
  - `GetInvalidInvoiceTest.php` - 查詢作廢發票測試（12 個測試）
  - `InvoiceNotifyTest.php` - 發票通知測試（33 個測試）
  - `AESTest.php` - 加解密測試（12 個測試）
  - `RequestTest.php` - HTTP 請求測試（16 個測試）
  - `ContentTest.php` - 基礎類別測試（23 個測試）
- 在 `InvoiceTest` 中新增 9 個錯誤情境測試

### Changed (變更)
- 重新調整專案結構，將發票作業、查詢、通知類別分別移至 `src/Operations`, `src/Queries`, `src/Notifications`，並更新所有引用、範例與測試使用新的命名空間
- 將所有實作方法的回應型別從 `InvoiceInterface` 改為 `self`，改善型別推斷精確度
  - 影響檔案：Invoice.php, AllowanceInvoice.php, InvoiceNotify.php, InvalidInvoice.php, GetInvoice.php, GetInvalidInvoice.php, CheckLoveCode.php, CheckBarcode.php, AllowanceInvalid.php
- 優化 AES.php、Request.php、Content.php 等核心類別
- 更新 README.md 說明文件

### Removed (移除)
- 移除容量較大的 `ecpay_einvoice_v3_0_0.pdf`，改以 `docs/` 內的 Markdown 摘要與官方連結替代

### Improved (改善)
- 測試覆蓋率從 30% 提升至 95%+
- 所有測試採用單元測試而非整合測試，移除對外部 API 的依賴
- 使用 Mock 和反射技術提升測試品質
- 完整覆蓋邊界條件、錯誤情境、特殊字元和 Unicode 測試
- 所有 Fluent Interface 鏈式呼叫都有對應測試

### Fixed (修正)
- 修正 `.gitignore` 規則
- 移除 `.phpunit.result.cache` 快取檔案

## [2.0.0] - 2024-11-20

### Changed (變更)
- 升級 PHP 版本需求至 ^8.3
- 升級 PHPUnit 至 ^10.5
- 引入 PHP_CodeSniffer 並修正程式碼規範

## [1.3.0] - 2024-11-01

### Added (新增)
- 初始功能實作

[Unreleased]: https://github.com/CarlLee1983/ecpay-einvoice-B2C/compare/v4.1.0...HEAD
[4.1.0]: https://github.com/CarlLee1983/ecpay-einvoice-B2C/compare/v4.0.1...v4.1.0
[4.0.1]: https://github.com/CarlLee1983/ecpay-einvoice-B2C/compare/v4.0.0...v4.0.1
[4.0.0]: https://github.com/CarlLee1983/ecpay-einvoice-B2C/compare/v3.0.0...v4.0.0
[3.0.0]: https://github.com/CarlLee1983/ecpay-einvoice-B2C/compare/v2.3.0...v3.0.0
[2.3.0]: https://github.com/CarlLee1983/ecpay-einvoice-B2C/compare/v2.2.0...v2.3.0
[2.2.0]: https://github.com/CarlLee1983/ecpay-einvoice-B2C/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/CarlLee1983/ecpay-einvoice-B2C/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/CarlLee1983/ecpay-einvoice-B2C/compare/v1.3.0...v2.0.0
[1.3.0]: https://github.com/CarlLee1983/ecpay-einvoice-B2C/releases/tag/v1.3.0

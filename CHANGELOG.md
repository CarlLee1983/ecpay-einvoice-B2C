# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/zh-TW/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/lang/zh-TW/).

## [2.1.0] - 2024-11-22

### Added (新增)
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
- 將所有實作方法的回應型別從 `InvoiceInterface` 改為 `self`，改善型別推斷精確度
  - 影響檔案：Invoice.php, AllowanceInvoice.php, InvoiceNotify.php, InvalidInvoice.php, 
    GetInvoice.php, GetInvalidInvoice.php, CheckLoveCode.php, CheckBarcode.php, AllowanceInvalid.php
- 優化 AES.php、Request.php、Content.php 等核心類別
- 更新 README.md 說明文件

### Improved (改善)
- 測試覆蓋率從 30% 提升至 95%+
- 所有測試採用單元測試而非整合測試，移除對外部 API 的依賴
- 使用 Mock 和反射技術提升測試品質
- 完整覆蓋邊界條件、錯誤情境、特殊字元和 Unicode 測試
- 所有 Fluent Interface 鏈式呼叫都有對應測試

### Fixed (修正)
- 修正 `.gitignore` 規則
- 移除 `.phpunit.result.cache` 快取檔案

## [2.0.0] - 2024-XX-XX

### Changed
- 升級 PHP 版本需求至 ^8.3
- 升級 PHPUnit 至 ^10.5
- 引入 PHP_CodeSniffer 並修正程式碼規範

## [1.3.0] - YYYY-MM-DD

### Added
- 初始功能實作

[2.1.0]: https://github.com/your-repo/ecpay-einvoice-B2B/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/your-repo/ecpay-einvoice-B2B/compare/v1.3.0...v2.0.0
[1.3.0]: https://github.com/your-repo/ecpay-einvoice-B2B/releases/tag/v1.3.0


# API 介接重點

## 1. 典型整合流程

1. **準備店家金鑰**：取得 `MerchantID`、`HashKey`、`HashIV`，與 API Server (測試/正式) URL。
2. **選擇模組類別**：依作業型態建立 `Operations`, `Queries`, `Notifications` 模組中的類別實例。所有類別都繼承 `Content`，共用 `setMerchantID()`、`setHashKey()`、`setHashIV()` 等方法。
3. **填入欄位並呼叫 `getPayload()`**：各類別提供 Fluent Interface 設定必填欄位，`RqHeader` 由 `DTO\RqHeaderDto` 管理、商品項目則由 `DTO\ItemCollection` 及各項目 DTO 組成；`getPayload()` 僅回傳純領域資料，若要自行送出可交由 `PayloadEncoder` 產製傳輸用 `Data`。
4. **透過 `EcPayClient` 發送請求**：`EcPayClient::send(CommandInterface $command)` 只負責送出已封裝的命令；命令（通常為 `Content` 子類）準備好 `getPayload()`/`getPayloadEncoder()`，Client 會注入金鑰後送往 `{Server}{RequestPath}` 並解密回應。
5. **解析 `Response`**：`Response::success()` 判斷 `RtnCode == 1` 是否成功，`getData()` 取得解密後的內容。

> 備註：PDF 版本記載更多進階參數（如批次作業、愛心碼維護等），若本摘要未涵蓋請回查官方文件。

### Laravel 協調器快速示範

在 Laravel 專案中，套件會註冊 `OperationCoordinator`，可透過 Facade 直接完成「建立 → 設定 → 送出」的流程：

```php
use EcPayInvoice;

$response = EcPayInvoice::issue(function ($invoice) {
    $invoice->setRelateNumber('INV' . now()->format('YmdHis'))
        ->setCustomerEmail('demo@example.com')
        ->setItems([
            [
                'name' => '商品Ａ',
                'quantity' => 1,
                'unit' => '個',
                'price' => 100,
            ],
        ]);
});

if ($response->success()) {
    // ...
}
```

查詢類別同樣可以使用 `EcPayQuery::coordinate('get_invoice', fn ($query) => ...)`。

## 2. 模組與端點對照

| 模組 | 類別 | Request Path | 用途 |
| --- | --- | --- | --- |
| Operations | `Invoice` | `/B2CInvoice/Issue` | 一般發票開立 |
| Operations | `DelayIssue` | `/B2CInvoice/DelayIssue` | 延遲/預約開立發票 |
| Operations | `EditDelayIssue` | `/B2CInvoice/EditDelayIssue` | 編輯延遲開立發票 |
| Operations | `TriggerIssue` | `/B2CInvoice/TriggerIssue` | 觸發延遲開立 |
| Operations | `CancelDelayIssue` | `/B2CInvoice/CancelDelayIssue` | 取消延遲開立 |
| Operations | `InvalidInvoice` | `/B2CInvoice/Invalid` | 作廢發票 |
| Operations | `AllowanceInvoice` | `/B2CInvoice/Allowance` | 開立折讓 |
| Operations | `AllowanceByCollegiate` | `/B2CInvoice/AllowanceByCollegiate` | 線上開立折讓（通知） |
| Operations | `AllowanceInvalidByCollegiate` | `/B2CInvoice/AllowanceInvalidByCollegiate` | 取消線上折讓 |
| Operations | `AllowanceInvalid` | `/B2CInvoice/AllowanceInvalid` | 作廢折讓 |
| Operations | `AddInvoiceWordSetting` | `/B2CInvoice/AddInvoiceWordSetting` | 設定字軌與配號 |
| Operations | `UpdateInvoiceWordStatus` | `/B2CInvoice/UpdateInvoiceWordStatus` | 更新字軌啟用狀態 |
| Printing | `InvoicePrint` | `/B2CInvoice/InvoicePrint` | 取得發票列印頁 |
| Queries | `GetInvoice` | `/B2CInvoice/GetIssue` | 查詢已開立發票 |
| Queries | `GetIssueList` | `/B2CInvoice/GetIssueList` | 查詢特定多筆發票 |
| Queries | `GetAllowanceList` | `/B2CInvoice/GetAllowanceList` | 查詢折讓明細 |
| Queries | `GetAllowanceInvalid` | `/B2CInvoice/GetAllowanceInvalid` | 查詢作廢折讓明細 |
| Queries | `GetInvalidInvoice` | `/B2CInvoice/GetInvalid` | 查詢作廢發票 |
| Queries | `GetInvoiceWordSetting` | `/B2CInvoice/GetInvoiceWordSetting` | 查詢字軌使用狀態 |
| Queries | `GetGovInvoiceWordSetting` | `/B2CInvoice/GetGovInvoiceWordSetting` | 查詢財政部字軌配號結果 |
| Queries | `CheckBarcode` | `/B2CInvoice/CheckBarcode` | 驗證手機條碼載具 |
| Queries | `CheckLoveCode` | `/B2CInvoice/CheckLoveCode` | 驗證愛心碼 |
| Notifications | `InvoiceNotify` | `/B2CInvoice/InvoiceNotify` | 發送開立/折讓/中獎通知 |
| Printing | （預留） | — | 未來列印相關 API |

## 3. 共用欄位與請求格式

### 3.1 RqHeader & 基本欄位

| 欄位 | 來源 | 說明 |
| --- | --- | --- |
| `MerchantID` | `Content::__construct` | 自動帶入建構子傳入之特店代號 |
| `RqHeader.Timestamp` | `Content::__construct` | 透過 `RqHeaderDto` 產生 UNIX timestamp，預設為建立實例當下時間 |
| `Data` | 各模組 `initContent()` | 真正的業務欄位，可透過 `PayloadEncoder` JSON encode → urlencode → AES 加密 |

### 3.2 加解密流程

1. `getPayload()` 或 `validation()` 會先確保必填欄位存在。
2. `PayloadEncoder` 將 `Data` JSON encode → urlencode → 進行 .NET 相容轉換。
3. `CipherService` 使用 AES-128-CBC/PKCS7（搭配 `HashKey`/`HashIV`）產出最終 `Data` 值。
4. 回應時 `EcPayClient` 會呼叫 `PayloadEncoder::decodeData()` 解出原始欄位，若 `Data` 空則退回 `TransCode`/`TransMsg`。

### 3.3 常見欄位（節錄）

| 欄位 | 類型 | 描述 |
| --- | --- | --- |
| `RelateNumber` | string(30) | 商家自訂唯一值，許多查詢/作廢流程必須一致 |
| `InvoiceNo` | string(10) | 由綠界產生的發票號碼，查詢/作廢必填 |
| `InvoiceDate` | `Y-m-d` | 開立日期；`Content::setInvoiceDate()` 會檢查格式 |
| `CustomerName/Addr/Phone/Email` | string | 依列印或通知需求而定；驗證邏輯見 `InvoiceValidator` |
| `CarrierType/CarrierNum` | enum/string | 依載具類型規則檢查長度與是否可列印 |
| `Print` | enum | `PrintMark::YES/NO`，與統編、捐贈、載具互斥規則相關 |
| `Donation/LoveCode` | enum/string | 捐贈時必須帶愛心碼，且不可列印 |
| `Items` | array/DTO | 由 `ItemCollection` + 對應項目 DTO (`InvoiceItemDto`, `AllowanceItemDto`, ...) 組成，會自動計算金額與套用缺省稅別 |

## 4. 驗證重點摘要

- **RelateNumber**：必填，且長度上限 30，建議自訂可追蹤。
- **TaxType vs ClearanceMark**：免稅或零稅率時需填入通關註記。
- **客戶聯絡資訊**：若不列印，至少要有 `CustomerPhone` 或 `CustomerEmail` 其一。
- **統編與列印**：有統編就必須列印 (`PrintMark::YES`)，且不得設定捐贈。
- **捐贈**：必須提供 `LoveCode`，且不可列印。
- **載具**：
  - `CarrierType::NONE` 時 `CarrierNum` 必須為空。
  - Citizen 載具長度 16，手機條碼長度 8。
  - 只要使用任何載具就無法列印。
- **Items**：不得為空，`SalesAmount` 若手動設定必須與項目加總相同。

> 更多欄位及例外情境建議核對官方 PDF；以上僅涵蓋專案程式內部的驗證邏輯。

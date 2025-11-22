# API 介接重點

## 1. 典型整合流程

1. **準備店家金鑰**：取得 `MerchantID`、`HashKey`、`HashIV`，與 API Server (測試/正式) URL。
2. **選擇模組類別**：依作業型態建立 `Operations`, `Queries`, `Notifications` 模組中的類別實例。所有類別都繼承 `Content`，共用 `setMerchantID()`、`setHashKey()`、`setHashIV()` 等方法。
3. **填入欄位並呼叫 `getContent()`**：各類別提供 Fluent Interface 設定必填欄位；`getContent()` 會驗證資料、將 `Data` JSON 化後再透過 AES/CBC 加密。
4. **透過 `EcPayClient` 發送請求**：`EcPayClient::send()` 會組成 `Request`，傳送至 `{Server}{RequestPath}` 並自動解密回應。
5. **解析 `Response`**：`Response::success()` 判斷 `RtnCode == 1` 是否成功，`getData()` 取得解密後的內容。

> 備註：PDF 版本記載更多進階參數（如批次作業、愛心碼維護等），若本摘要未涵蓋請回查官方文件。

## 2. 模組與端點對照

| 模組 | 類別 | Request Path | 用途 |
| --- | --- | --- | --- |
| Operations | `Invoice` | `/B2CInvoice/Issue` | 一般發票開立 |
| Operations | `InvalidInvoice` | `/B2CInvoice/Invalid` | 作廢發票 |
| Operations | `AllowanceInvoice` | `/B2CInvoice/Allowance` | 開立折讓 |
| Operations | `AllowanceInvalid` | `/B2CInvoice/AllowanceInvalid` | 作廢折讓 |
| Queries | `GetInvoice` | `/B2CInvoice/GetIssue` | 查詢已開立發票 |
| Queries | `GetInvalidInvoice` | `/B2CInvoice/GetInvalid` | 查詢作廢發票 |
| Queries | `CheckBarcode` | `/B2CInvoice/CheckBarcode` | 驗證手機條碼載具 |
| Queries | `CheckLoveCode` | `/B2CInvoice/CheckLoveCode` | 驗證愛心碼 |
| Notifications | `InvoiceNotify` | `/B2CInvoice/InvoiceNotify` | 發送開立/折讓/中獎通知 |
| Printing | （預留） | — | 未來列印相關 API |

## 3. 共用欄位與請求格式

### 3.1 RqHeader & 基本欄位

| 欄位 | 來源 | 說明 |
| --- | --- | --- |
| `MerchantID` | `Content::__construct` | 自動帶入建構子傳入之特店代號 |
| `RqHeader.Timestamp` | `Content::__construct` | UNIX timestamp，預設為建立實例當下時間 |
| `RqHeader.RqID` | `Content::getRqID()` | 由時間戳與亂數組成的唯一識別碼 |
| `RqHeader.Revision` | `Content::VERSION` | 目前為 `3.0.0`，代表 API 版本 |
| `Data` | 各模組 `initContent()` | 真正的業務欄位，後續會 JSON encode → urlencode → AES 加密 |

### 3.2 加解密流程

1. `getContent()` 會先執行 `validation()`，確保必填欄位存在。
2. `Data` 以 `json_encode` 序列化，並轉為 URL-safe 字串。
3. 使用 `AES::encrypt()` 搭配 `HashKey`/`HashIV`（CBC/PKCS7）產出最終 `Data` 值。
4. 回應時 `EcPayClient` 會解密 `Data`，失敗則回傳 `TransCode`/`TransMsg`。

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
| `Items` | array | 每筆需包含 `name`, `quantity`, `unit`, `price`；系統會自動計算金額 |

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

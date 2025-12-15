# API 介接重點

## 介接注意事項

在開始整合前，請詳閱以下注意事項（參考[官方介接注意事項](https://developers.ecpay.com.tw/?p=7854)）：

### 服務申請

- 若要使用電子發票服務，需先向綠界提出申請方可使用。

### 連線與安全性

| 項目 | 說明 |
| --- | --- |
| 連線方式 | 僅提供 **HTTPS (443 port)** 連線，並使用合法的 DNS 進行介接。 |
| 加密協定 | 支援 **TLS 1.2** 加密通訊協定，確保傳輸安全。 |
| 傳送方式 | 各項交易參數須使用 **HTTP POST** 方式傳送。 |
| 回傳 Port | 特店伺服器 URL 連接 port 為 http 80 / https 443。 |

### 防火牆設定

若需開通防火牆連線至綠界主機，因 IP 不固定，請以 FQDN 方式設定：

| 環境 | Domain | Port |
| --- | --- | --- |
| 正式環境 | `einvoice.ecpay.com.tw` | TCP 443 |
| 測試環境 | `einvoice-stage.ecpay.com.tw` | TCP 443 |

> 如需固定 IP，請至 <https://member.ecpay.com.tw/ServiceReply/CreateProblem> 申請，問題類別選擇「申請主機IP鎖定」。

### 金鑰安全警告

- **請勿將金鑰資訊（HashKey、HashIV）存放或顯示於前端網頁**，如 JavaScript、HTML、CSS 等，避免金鑰被盜取使用造成損失及交易資料外洩。
- 建議透過 `.env` 檔案或環境變數管理金鑰，並確保不納入版本控制。

### 網址限制

- 回傳網址**不支援中文網址**，若有中文網域請使用 **punycode** 編碼轉換。
- 例如：`中文.tw` 需改為 `xn--fiq228c.tw`

### 列印相關申請

| 需求 | 說明 |
| --- | --- |
| 超商 KIOSK 列印 | 除須向業務人員申請外，請參照開立發票列印相關參數特別說明。 |
| 自行列印電子發票 | 需申請密碼種子，請聯繫業務人員辦理。 |

### API 呼叫限制

- 若程式呼叫 API 速度過快，會收到 **HTTP 403** 狀態碼，請降低呼叫速度並**等候 30 分鐘**後再重新呼叫。
- 如有高速存取需求，請確認具備「特約賣家」身分，並聯繫所屬業務人員。
- 收到 **HTTP 500** 狀態碼時，可能原因包含：資料格式錯誤、MerchantID 與 Key/IV 不匹配（無權限）或加解密錯誤。

---

## 1. 典型整合流程

1. **準備店家金鑰**：取得 `MerchantID`、`HashKey`、`HashIV`，與 API Server (測試/正式) URL。
2. **選擇模組類別**：依作業型態建立 `Operations`, `Queries`, `Notifications` 模組中的類別實例。所有類別都繼承 `Content`，共用 `setMerchantID()`、`setHashKey()`、`setHashIV()` 等方法。
3. **填入欄位並（可選）檢視 payload**：各類別提供 Fluent Interface 設定必填欄位，`RqHeader` 由 `DTO\RqHeaderDto` 管理、商品項目則由 `DTO\ItemCollection` 及各項目 DTO 組成；你可用 `getPayload()` 檢視未加密的 payload（含 `MerchantID`/`RqHeader`/`Data`），或用 `getTransportBody()` 取得可傳輸的加密內容（`Data` 已加密；`getContent()` 為相容名稱）。
4. **透過 `EcPayClient` 發送請求**：`EcPayClient::send(SendableCommandInterface $command)` 只接受可產生加密傳輸內容、且能解碼回應的命令（通常為 `Content` 子類）；Client 會注入金鑰後呼叫 `getTransportBody()`（或 `getContent()`），送往 `{Server}{RequestPath}`，並將回應交由命令的 `decodeResponse()` 解碼。
5. **解析 `Response`**：`Response::success()` 判斷 `RtnCode == 1` 是否成功，`getData()` 取得解密後的內容。

> 備註：PDF 版本記載更多進階參數（如批次作業、愛心碼維護等），若本摘要未涵蓋請回查官方文件。

### Laravel 協調器快速示範

在 Laravel 專案中，套件會註冊 `OperationCoordinator`，可透過 Facade 直接完成「建立 → 設定 → 送出」的流程：

```php
use CarlLee\EcPayB2C\DTO\InvoiceItemDto;
use CarlLee\EcPayB2C\Laravel\Facades\EcPayInvoice;

$response = EcPayInvoice::issue(function ($invoice) {
    $invoice->setRelateNumber('INV' . now()->format('YmdHis'))
        ->setCustomerEmail('demo@example.com')
        ->setItems([
            InvoiceItemDto::fromArray([
                'name' => '商品Ａ',
                'quantity' => 1,
                'unit' => '個',
                'price' => 100,
            ]),
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
| Operations | `InvoicePrint` | `/B2CInvoice/InvoicePrint` | 取得發票列印頁 |
| Queries | `GetInvoice` | `/B2CInvoice/GetIssue` | 查詢已開立發票 |
| Queries | `GetIssueList` | `/B2CInvoice/GetIssueList` | 查詢特定多筆發票 |
| Queries | `GetAllowanceList` | `/B2CInvoice/GetAllowanceList` | 查詢折讓明細 |
| Queries | `GetAllowanceInvalid` | `/B2CInvoice/GetAllowanceInvalid` | 查詢作廢折讓明細 |
| Queries | `GetInvalidInvoice` | `/B2CInvoice/GetInvalid` | 查詢作廢發票 |
| Queries | `GetInvoiceWordSetting` | `/B2CInvoice/GetInvoiceWordSetting` | 查詢字軌使用狀態 |
| Queries | `GetGovInvoiceWordSetting` | `/B2CInvoice/GetGovInvoiceWordSetting` | 查詢財政部字軌配號結果 |
| Queries | `CheckBarcode` | `/B2CInvoice/CheckBarcode` | 驗證手機條碼載具 |
| Queries | `CheckLoveCode` | `/B2CInvoice/CheckLoveCode` | 驗證愛心碼 |
| Queries | `GetCompanyNameByTaxID` | `/B2CInvoice/GetCompanyNameByTaxID` | 依統一編號查詢公司名稱 |
| Notifications | `InvoiceNotify` | `/B2CInvoice/InvoiceNotify` | 發送開立/折讓/中獎通知 |

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

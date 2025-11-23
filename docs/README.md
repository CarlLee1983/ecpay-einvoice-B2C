# 綠界電子發票 API 摘要

## 文件結構

- `api-overview.md`：介接流程、模組分類、請求/回應格式與常見參數。
- `error-codes.md`：常見錯誤碼與專案內部驗證例外對照。

## 官方資源
- 技術支援：<https://developers.ecpay.com.tw/?p=7809>

> 建議在專案升級或綠界公告有修訂時，檢查官方文件是否更新並同步調整本摘要。

## Laravel 整合重點

1. Service Provider：`ecPay\eInvoice\Laravel\EcPayServiceProvider` 會自動載入，或可手動加入 `config/app.php`。
2. 設定檔：執行 `php artisan vendor:publish --tag=ecpay-einvoice-config` 生成 `config/ecpay-einvoice.php`，設定 MerchantID / HashKey / HashIV。
3. Service Container：`app('ecpay.invoice')`、`app('ecpay.query_invoice')` 等 key 將回傳對應的操作物件，可由 `config/ecpay-einvoice.php` 的 `bindings` 區段調整。
4. Facade：`EcPayInvoice` 與 `EcPayQuery` 皆繼承 `OperationFactory`，可透過 `EcPayInvoice::make()`、`EcPayQuery::invoice()` 快速取得常用類別。

---

## Files
- `api-overview.md`: onboarding flow, module list, shared request/response shapes
- `error-codes.md`: frequently observed API errors and the corresponding in-code validation hints

## Official Resources
- API support: <https://developers.ecpay.com.tw/?p=7809>

> When ECPay announces updates, re-sync these notes to stay aligned with production behavior.

---

## 使用流程圖 / Flowcharts

以下流程根據官方 B2C 電子發票技術文件的「使用流程圖說明」整理，提供中英文並列的 mermaid 圖示，方便快速瀏覽整體作業節點[[1]](https://developers.ecpay.com.tw/?p=7829)。

### 前置設定 / Pre-Operation Setup
```mermaid
flowchart TD
    A([啟動整合 Start]) --> B[簽約並取得 MerchantID/HashKey/HashIV]
    B --> C{選擇環境?}
    C -->|測試| D[設定測試 Server / 金鑰]
    C -->|正式| E[設定正式 Server / 金鑰]
    D --> F[查詢財政部配號結果 / GetGovInvoiceWordSetting]
    E --> F
    F --> G[設定字軌與配號 / AddInvoiceWordSetting]
    G --> H[啟用字軌 / UpdateInvoiceWordStatus]
    H --> I([完成前置準備 Ready])
```

### 開立發票 / Issue Invoice
```mermaid
flowchart TD
    A([開始 Start]) --> B[組裝發票資料（Items、客戶資訊等）]
    B --> C[套用驗證規則（InvoiceValidator）]
    C -->|失敗| C1[補齊或修正欄位] --> B
    C -->|成功| D[呼叫 /B2CInvoice/Issue]
    D --> E{API 回應?}
    E -->|成功| F[回寫發票號碼與追蹤資訊]
    E -->|失敗| G[記錄錯誤並進行補償流程]
    F --> H([流程結束 Done])
    G --> H
```

### 折讓 / 作廢 / 註銷流程  
Allowance / Void / Reissue Flow
```mermaid
flowchart TD
    A([事件觸發 Trigger]) --> B{需求類型?}
    B -->|折讓 Allowance| C[建立折讓資料 / AllowanceInvoice]
    B -->|作廢 Void| D[準備作廢資訊 / InvalidInvoice]
    B -->|註銷重開 Reissue| E[取消或重開作業 / CancelDelay/TriggerIssue]

    C --> C1[檢核金額與品項] --> C2{通知方式?}
    C2 -->|紙本| C3[紙本折讓] --> H
    C2 -->|線上| C4[線上通知 / AllowanceByCollegiate] --> H

    D --> D1[檢查原發票狀態]
    D1 -->|可作廢| D2[呼叫 /B2CInvoice/Invalid] --> H
    D1 -->|不可作廢| D3[提示錯誤] --> H

    E --> E1[確認延遲/預約狀態]
    E1 -->|取消| E2[呼叫 CancelDelayIssue] --> H
    E1 -->|觸發開立| E3[呼叫 TriggerIssue] --> H

    H([流程結束 Done])
```

> 若需放大細節或補充其他流程，可在此章節持續擴充新的 mermaid 圖表。

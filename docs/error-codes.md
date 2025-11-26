# 錯誤碼與驗證訊息對照

## HTTP 狀態碼

在呼叫綠界 API 時，可能會收到以下 HTTP 狀態碼，請依據狀況進行處理：

| HTTP 狀態碼 | 原因 | 處理方式 |
| --- | --- | --- |
| `200` | 請求成功，回應內容包含 `TransCode`/`TransMsg` 與加密的 `Data`。 | 解析回應並檢查 `RtnCode` 判斷業務邏輯是否成功。 |
| `403` | API 呼叫頻率過快，被綠界主機限制。 | 降低呼叫速度，**等待 30 分鐘**後再重新呼叫。如有高速存取需求，請確認具備「特約賣家」身分並聯繫業務人員。 |
| `500` | 伺服器端錯誤，可能原因包含：<br>- 資料格式錯誤<br>- MerchantID 與 HashKey/HashIV 不匹配（無權限）<br>- 加解密錯誤 | 檢查收到的回傳訊息，確認 `MerchantID`、`HashKey`、`HashIV` 是否正確配對，並檢查請求資料格式後重新傳送。 |

> 若持續收到錯誤，建議將完整請求與回應內容記錄下來，並聯繫綠界技術支援。

---

## 1. API `RtnCode` 概覽

| RtnCode | 說明 | 處理建議 |
| --- | --- | --- |
| `1` | 成功。伺服器會於 `Data` 中回傳實際結果（例如發票號碼）。 | 直接解析 `Response::getData()` 後續處理。 |
| 其他（官方定義） | 由綠界後端回報的錯誤，常見於欄位不符或系統維護。 | 依 `RtnMsg`/`TransMsg` 判斷；若為欄位錯誤，請參考下方「內部驗證」列表或官方 PDF 詳細說明。 |

> 完整 `RtnCode` 列表請參考官方 PDF，或於實際回傳時記錄 `RtnMsg`/`TransCode` 以利除錯。

## 2. 本套件內部驗證例外

在送出 API 前，程式會先透過 `InvoiceValidator` 與各模組的 `validation()` 進行檢查；若不符合條件會直接丟出 `Exception`。以下彙整常見訊息：

### 2.1 基本連線/身分

| 訊息 | 觸發條件 |
| --- | --- |
| `MerchantID is empty.` | 未設定 `setMerchantID()` 或建構子沒有帶入 `MerchantID`。 |
| `HashKey is empty.` / `HashIV is empty.` | `EcPayClient` 未注入金鑰，或手動建立類別未設定。 |
| `The invoice date format is invalid.` | `setInvoiceDate()` 非 `Y-m-d` 格式。 |

### 2.2 發票開立常見錯誤

| 訊息 | 說明 |
| --- | --- |
| `The invoice RelateNumber is empty.` | `RelateNumber` 為必填且需唯一。 |
| `Invoice is duty free, clearance mark can not be empty.` | 當 `TaxType` 為零稅率/免稅需填 `ClearanceMark`。 |
| `Because print mark is yes. Customer name and address can not be empty.` | 列印發票時必須提供客戶姓名與地址。 |
| `You should be settings either of customer phone and email.` | 至少填一種聯絡方式。 |
| `Because customer identifier not empty, print mark must be Yes` | 有統編必須列印。 |
| `Customer identifier not empty, donation can not be yes.` | 統編與捐贈互斥。 |
| `Donation is yes, love code required.` | 捐贈需要愛心碼。 |
| `Donation is yes, invoice can not be print.` | 捐贈不可列印。 |
| `Invoice carrier type is empty, carrier number must be empty.` | 未指定載具類型時不得填載具號碼。 |
| `Carrier type is not empty, invoice can not be print.` | 使用任何載具都不能列印。 |
| `Invoice carrier type is member, carrier number must be empty.` | 會員載具不需填號碼。 |
| `Invoice carrier type is citizen, carrier number length must be 16.` | 自然人憑證載具長度限制。 |
| `Invoice carrier type is Cellphone, carrier number length must be 8.` | 手機條碼長度限制。 |
| `Invoice data items is Empty.` | 商品項目不得為空。 |
| `The calculated sales amount is not equal to the set sales amount.` | 手動設定的 `SalesAmount` 與項目金額相加不符。 |

### 2.3 查詢/折讓/通知常見錯誤

| 模組 | 訊息 | 說明 |
| --- | --- | --- |
| GetInvoice / GetInvalidInvoice | `The invoice no length should be 10.` / `The invoice no is empty.` | 查詢發票需填完整 10 碼發票號。 |
| AllowanceInvoice | `The invoice allowance notify type is invalid.`、`Customer name is empty.`、`Phone number length must be less than 21 characters.` | 折讓通知欄位格式與長度檢查。 |
| AllowanceInvalid | `The invoice allowance no length should be 16.` | 作廢折讓需填 16 碼折讓單號。 |
| InvoiceNotify | `Phone number or mail should be set.`、`Invoice tag is empty.`、`Notified is empty.` | 通知 API 需指定發票/折讓標記與聯絡方式。 |

> 建議在除錯時將例外訊息一併記錄；若訊息不在上述列表，代表邏輯可能來自其他模組，需對照對應 PHP 類別或官方文件。

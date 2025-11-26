# Release Notes - v4.0.0

## 🎉 版本資訊

**版本號**：v4.0.0  
**發布日期**：2025-11-26  
**類型**：主版本更新（含破壞性變更）

---

## ⚠️ 破壞性變更 (Breaking Changes)

### Parameter 類別改為 Enum

所有 `src/Parameter/` 目錄下的常數類別已改為 PHP 8.1 String-backed Enum。這會影響所有使用這些常數的程式碼。

**受影響的類別**：
- `AllowanceNotifyType`
- `CarrierType`
- `ClearanceMark`
- `Donation`
- `InvoiceTagType`
- `InvType`
- `NotifiedType`
- `NotifyType`
- `PrintMark`
- `SpecialTaxType`
- `TaxType`
- `VatType`

**使用方式變更**：

```php
// ❌ 舊寫法（v3.x）
$invoice->setTaxType(TaxType::DUTIABLE);
$invoice->setPrintMark(PrintMark::NO);
$invoice->setDonation(Donation::NO);
$invoice->setCarrierType(CarrierType::MEMBER);

// ✅ 新寫法（v4.0）
$invoice->setTaxType(TaxType::DUTIABLE->value);
$invoice->setPrintMark(PrintMark::NO->value);
$invoice->setDonation(Donation::NO->value);
$invoice->setCarrierType(CarrierType::MEMBER->value);
```

**Enum 的優勢**：
- 類型安全：IDE 可自動完成和錯誤檢查
- 可列舉：使用 `TaxType::cases()` 取得所有可用值
- 可驗證：使用 `TaxType::tryFrom('1')` 安全解析

---

## ✨ 主要更新

### 1. PHP 8.3 Typed Class Constants 📝

所有常數加上類型宣告，提升類型安全性：

```php
// Content.php
public const int RELATE_NUMBER_MAX_LENGTH = 30;
public const int RQID_RANDOM_LENGTH = 5;
```

### 2. PHP 8.3 `#[\Override]` 屬性 🏷️

為所有實作介面的方法加上 `#[\Override]` 屬性，確保方法簽名正確：

**受影響的類別**：
- `InvoiceItemDto`, `AllowanceItemDto`, `AllowanceCollegiateItemDto`：`fromArray()`, `toPayload()`, `getAmount()`
- `ItemCollection`：`getIterator()`, `count()`
- `Content`：`getRequestPath()`, `setHashKey()`, `setHashIV()`, `getPayload()`, `getPayloadEncoder()`

### 3. PHP 8.2 Readonly Classes 🔒

DTO 類別改為不可變的 `readonly class`：

```php
final readonly class InvoiceItemDto implements ItemDtoInterface
{
    // 所有屬性自動成為 readonly
}
```

**受影響的類別**：
- `InvoiceItemDto`
- `AllowanceItemDto`
- `AllowanceCollegiateItemDto`

### 4. PHP 8.0 Constructor Property Promotion ⚡

簡化建構子，減少冗餘程式碼：

```php
// CipherService
public function __construct(
    private readonly string $hashKey,
    private readonly string $hashIV,
) { }

// PayloadEncoder
public function __construct(
    private readonly CipherService $cipherService,
) { }

// OperationCoordinator
public function __construct(
    private readonly OperationFactoryInterface $factory,
    private readonly EcPayClient $client,
) { }
```

### 5. 所有 Parameter 類別加入 `declare(strict_types=1)` ✅

提升型別安全性，確保嚴格的類型檢查。

---

## 📊 變更統計

| 項目 | 數量 |
|------|------|
| 升級為 Enum 的類別 | 12 |
| 改為 readonly class 的類別 | 3 |
| 加上 `#[\Override]` 的方法 | 16 |
| 加上類型宣告的常數 | 14 |
| 使用 constructor property promotion 的類別 | 3 |
| 更新的測試檔案 | 5 |
| 更新的範例檔案 | 3 |

---

## 🔄 遷移指南

### 步驟 1：更新 Composer 依賴

```bash
composer require "carllee1983/ecpay-einvoice-b2c:^4.0"
```

### 步驟 2：更新 Parameter 常數使用

搜尋並替換所有 Parameter 常數的使用：

```bash
# 使用 sed 批量替換（範例）
find . -name "*.php" -exec sed -i '' \
  -e 's/TaxType::DUTIABLE\([^-]\)/TaxType::DUTIABLE->value\1/g' \
  -e 's/PrintMark::NO\([^-]\)/PrintMark::NO->value\1/g' \
  -e 's/Donation::NO\([^-]\)/Donation::NO->value\1/g' \
  {} \;
```

### 步驟 3：更新比較邏輯（如有）

如果你的程式碼有比較 Enum 值：

```php
// ❌ 舊寫法
if ($data['TaxType'] == TaxType::DUTIABLE) { }

// ✅ 新寫法
if ($data['TaxType'] == TaxType::DUTIABLE->value) { }

// 💡 或使用 Enum 的原生方法
if (TaxType::tryFrom($data['TaxType']) === TaxType::DUTIABLE) { }
```

### 步驟 4：執行測試

```bash
composer test
```

---

## 🧪 測試與品質

- **281 個測試案例**
- **600+ 個斷言**
- **所有 PHPCS 規範通過**
- **相容 PHP 8.3.24**

---

## 📚 Enum 使用範例

### 列舉所有可用值

```php
use CarlLee\EcPayB2C\Parameter\TaxType;

// 取得所有案例
foreach (TaxType::cases() as $case) {
    echo $case->name . ' => ' . $case->value . PHP_EOL;
}
// 輸出：
// DUTIABLE => 1
// ZERO => 2
// FREE => 3
// MIX => 9
```

### 從值解析 Enum

```php
// 安全解析（失敗回傳 null）
$taxType = TaxType::tryFrom('1'); // TaxType::DUTIABLE

// 強制解析（失敗拋出例外）
$taxType = TaxType::from('1'); // TaxType::DUTIABLE
```

### 在 switch 中使用

```php
match ($taxType) {
    TaxType::DUTIABLE => '應稅',
    TaxType::ZERO => '零稅率',
    TaxType::FREE => '免稅',
    TaxType::MIX => '混合稅',
};
```

---

## 🚀 發佈流程

```bash
git checkout master
git pull origin master
composer test
git tag v4.0.0
git push origin master --tags
```

---

## 📦 完整 Commit 記錄

| Commit | 說明 |
|--------|------|
| `2a77d35` | refactor: [Parameter] 為常數類別加入 PHP 8.3 typed constants |
| `877520e` | refactor: [DTO] 加入 PHP 8.3 #[\Override] 屬性標記介面實作方法 |
| `32a0812` | refactor: [DTO] 將 Item DTO 類別改為 PHP 8.2 readonly class |
| `c92c686` | feat: [Parameter] 將常數類別改為 PHP 8.1 String-backed Enum |
| `7c57975` | refactor: [Infrastructure] 使用 PHP 8.0 constructor property promotion |

---

## 🙏 致謝

感謝所有使用者的回饋。如有任何問題或建議，歡迎透過 GitHub Issue 提出。


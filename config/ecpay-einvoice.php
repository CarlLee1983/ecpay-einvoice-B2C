<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | ECPay 介接環境
    |--------------------------------------------------------------------------
    |
    | 預設為綠界提供的測試環境。正式環境請改用
    | https://einvoice.ecpay.com.tw
    |
    */
    'server' => env('ECPAY_EINVOICE_SERVER', 'https://einvoice-stage.ecpay.com.tw'),

    /*
    |--------------------------------------------------------------------------
    | 商店憑證設定
    |--------------------------------------------------------------------------
    |
    | MerchantID/HashKey/HashIV 為綠界提供的專屬金鑰。
    |
    | ⚠️  安全性警告：
    | - 請勿將金鑰資訊（HashKey、HashIV）存放或顯示於前端網頁，
    |   如 JavaScript、HTML、CSS 等，避免金鑰被盜取造成損失及資料外洩。
    | - 務必透過 .env 檔案配置金鑰，並確保 .env 不納入版本控制（已於 .gitignore 排除）。
    | - 切勿將金鑰硬編碼於程式碼或設定檔中。
    |
    */
    'merchant_id' => env('ECPAY_EINVOICE_MERCHANT_ID', ''),
    'hash_key' => env('ECPAY_EINVOICE_HASH_KEY', ''),
    'hash_iv' => env('ECPAY_EINVOICE_HASH_IV', ''),

    /*
    |--------------------------------------------------------------------------
    | SSL 驗證設定
    |--------------------------------------------------------------------------
    |
    | 正式環境建議啟用 SSL 驗證（true）。
    | 測試環境如遇到憑證問題，可暫時關閉（false）。
    |
    */
    'verify_ssl' => env('ECPAY_EINVOICE_VERIFY_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | 工廠額外設定
    |--------------------------------------------------------------------------
    |
    | aliases: 自訂別名 => 類別對應，例如 'custom.invoice' => App\Invoice\CustomInvoice::class
    | initializers: 需要傳入可呼叫物件（建議使用 __invoke 類別），用來統一設定
    |               RelateNumber、預設欄位等邏輯。
    |
    */
    'factory' => [
        'aliases' => [
            // 'custom.invoice' => \App\Invoices\CustomInvoice::class,
        ],
        'initializers' => [
            // \App\Invoices\Initializers\DefaultRelateNumber::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 便利綁定
    |--------------------------------------------------------------------------
    |
    | 可經由 app('ecpay.invoice') / app('ecpay.query_invoice') 解析對應操作物件。
    | Facade 也會依照這裡的 key 對應，例如 EcPayInvoice::invoice()。
    |
    */
    'bindings' => [
        'invoice' => 'invoice',
        'allowance' => 'operations.allowance_invoice',
        'invalid_invoice' => 'operations.invalid_invoice',
        'invoice_print' => 'operations.invoice_print',
        'query_invoice' => 'queries.get_invoice',
        'query_invalid_invoice' => 'queries.get_invalid_invoice',
        'query_allowance' => 'queries.get_issue_list',
    ],
];

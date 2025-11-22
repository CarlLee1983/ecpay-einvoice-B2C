<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Operations;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\InvoiceInterface;
use ecPay\eInvoice\Parameter\AllowanceNotifyType;
use Exception;

class AllowanceByCollegiate extends Content
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/AllowanceByCollegiate';

    /**
     * 折讓項目陣列。
     *
     * @var array
     */
    protected $items = [];

    /**
     * 初始化內容。
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'InvoiceNo' => '',
            'InvoiceDate' => '',
            'AllowanceNotify' => AllowanceNotifyType::EMAIL,
            'CustomerName' => '',
            'NotifyMail' => '',
            'ReturnURL' => '',
            'AllowanceAmount' => 0,
            'Items' => [],
        ];
    }

    /**
     * 設定發票號碼。
     *
     * @param string $invoiceNo
     * @return InvoiceInterface
     */
    public function setInvoiceNo(string $invoiceNo): self
    {
        if (!preg_match('/^[A-Z]{2}[0-9]{8}$/', strtoupper($invoiceNo))) {
            throw new Exception('InvoiceNo 格式錯誤，需為 2 碼英文 + 8 碼數字。');
        }

        $this->content['Data']['InvoiceNo'] = strtoupper($invoiceNo);

        return $this;
    }

    /**
     * 設定客戶名稱。
     *
     * @param string $name
     * @return InvoiceInterface
     */
    public function setCustomerName(string $name): self
    {
        if (empty($name)) {
            throw new Exception('CustomerName 不可為空。');
        }

        $this->content['Data']['CustomerName'] = $name;

        return $this;
    }

    /**
     * 設定通知信箱，可接受多組以分號分隔。
     *
     * @param string $emails
     * @return InvoiceInterface
     */
    public function setNotifyMail(string $emails): self
    {
        $list = array_filter(array_map('trim', explode(';', $emails)));

        if (empty($list)) {
            throw new Exception('NotifyMail 至少需要一個有效 Email。');
        }

        foreach ($list as $email) {
            if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('NotifyMail 格式錯誤。');
            }
        }

        $this->content['Data']['NotifyMail'] = implode(';', $list);

        return $this;
    }

    /**
     * 設定折讓完成後回傳 URL。
     *
     * @param string $url
     * @return InvoiceInterface
     */
    public function setReturnURL(string $url): self
    {
        if (empty($url)) {
            throw new Exception('ReturnURL 不可為空。');
        }

        if (strlen($url) > 200) {
            throw new Exception('ReturnURL 長度需小於等於 200。');
        }

        $this->content['Data']['ReturnURL'] = $url;

        return $this;
    }

    /**
     * 設定折讓項目。
     *
     * @param array $items
     * @return InvoiceInterface
     */
    public function setItems(array $items): self
    {
        $required = ['name', 'quantity', 'unit', 'price', 'taxType'];

        $this->items = [];
        $this->content['Data']['AllowanceAmount'] = 0;

        foreach ($items as $item) {
            foreach ($required as $field) {
                if (!isset($item[$field])) {
                    throw new Exception('折讓項目缺少欄位: ' . $field);
                }
            }

            $amount = (float) $item['quantity'] * (float) $item['price'];

            $this->items[] = [
                'ItemName' => $item['name'],
                'ItemCount' => (float) $item['quantity'],
                'ItemWord' => $item['unit'],
                'ItemPrice' => (float) $item['price'],
                'ItemAmount' => $amount,
                'ItemTaxType' => (string) $item['taxType'],
            ];

            $this->content['Data']['AllowanceAmount'] += $amount;
        }

        return $this;
    }

    /**
     * 取得內容。
     *
     * @return array
     */
    public function getContent(): array
    {
        $this->content['Data']['Items'] = $this->items;

        return parent::getContent();
    }

    /**
     * 驗證內容。
     */
    public function validation()
    {
        $this->validatorBaseParam();

        if (empty($this->content['Data']['InvoiceNo'])) {
            throw new Exception('InvoiceNo 不可為空。');
        }

        if (empty($this->content['Data']['InvoiceDate'])) {
            throw new Exception('InvoiceDate 不可為空。');
        }

        if ($this->content['Data']['AllowanceNotify'] !== AllowanceNotifyType::EMAIL) {
            throw new Exception('AllowanaceNotify 僅支援電子郵件 (E)。');
        }

        if (empty($this->content['Data']['NotifyMail'])) {
            throw new Exception('NotifyMail 不可為空。');
        }

        if (empty($this->content['Data']['ReturnURL'])) {
            throw new Exception('ReturnURL 不可為空。');
        }

        if ($this->content['Data']['AllowanceAmount'] <= 0) {
            throw new Exception('AllowanceAmount 必須大於 0。');
        }

        if (empty($this->content['Data']['Items'])) {
            throw new Exception('折讓項目不可為空。');
        }
    }
}

<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Operations;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\InvoiceInterface;
use Exception;

class AllowanceInvalidByCollegiate extends Content
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/AllowanceInvalidByCollegiate';

    /**
     * 初始化內容。
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'InvoiceNo' => '',
            'AllowanceNo' => '',
            'Reason' => '',
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
     * 設定折讓編號。
     *
     * @param string $allowanceNo
     * @return InvoiceInterface
     */
    public function setAllowanceNo(string $allowanceNo): self
    {
        if (strlen($allowanceNo) !== 16) {
            throw new Exception('AllowanceNo 長度需為 16 碼。');
        }

        $this->content['Data']['AllowanceNo'] = $allowanceNo;

        return $this;
    }

    /**
     * 設定取消原因。
     *
     * @param string $reason
     * @return InvoiceInterface
     */
    public function setReason(string $reason): self
    {
        if (empty($reason)) {
            throw new Exception('Reason 不可為空。');
        }

        if (mb_strlen($reason) > 20) {
            throw new Exception('Reason 長度需小於等於 20。');
        }

        $this->content['Data']['Reason'] = $reason;

        return $this;
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

        if (empty($this->content['Data']['AllowanceNo'])) {
            throw new Exception('AllowanceNo 不可為空。');
        }

        if (empty($this->content['Data']['Reason'])) {
            throw new Exception('Reason 不可為空。');
        }
    }
}

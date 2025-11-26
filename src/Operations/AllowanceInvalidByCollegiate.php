<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Operations;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

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
     * @return self
     */
    public function setInvoiceNo(string $invoiceNo): self
    {
        if (!preg_match('/^[A-Z]{2}[0-9]{8}$/', strtoupper($invoiceNo))) {
            throw new InvalidParameterException('InvoiceNo 格式錯誤，需為 2 碼英文 + 8 碼數字。');
        }

        $this->content['Data']['InvoiceNo'] = strtoupper($invoiceNo);

        return $this;
    }

    /**
     * 設定折讓編號。
     *
     * @param string $allowanceNo
     * @return self
     */
    public function setAllowanceNo(string $allowanceNo): self
    {
        if (strlen($allowanceNo) !== 16) {
            throw new InvalidParameterException('AllowanceNo 長度需為 16 碼。');
        }

        $this->content['Data']['AllowanceNo'] = $allowanceNo;

        return $this;
    }

    /**
     * 設定取消原因。
     *
     * @param string $reason
     * @return self
     */
    public function setReason(string $reason): self
    {
        if (empty($reason)) {
            throw new InvalidParameterException('Reason 不可為空。');
        }

        if (mb_strlen($reason) > 20) {
            throw new InvalidParameterException('Reason 長度需小於等於 20。');
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
            throw new InvalidParameterException('InvoiceNo 不可為空。');
        }

        if (empty($this->content['Data']['AllowanceNo'])) {
            throw new InvalidParameterException('AllowanceNo 不可為空。');
        }

        if (empty($this->content['Data']['Reason'])) {
            throw new InvalidParameterException('Reason 不可為空。');
        }
    }
}

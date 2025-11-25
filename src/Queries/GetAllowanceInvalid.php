<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Queries;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\InvoiceInterface;
use Exception;

class GetAllowanceInvalid extends Content
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/GetAllowanceInvalid';

    /**
     * 初始化查詢內容。
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'InvoiceNo' => '',
            'AllowanceNo' => '',
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
        if (!preg_match('/^[A-Z]{2}[0-9]{8}$/', $invoiceNo)) {
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
     * 驗證內容。
     *
     * @return void
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
    }
}

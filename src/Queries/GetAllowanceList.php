<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Queries;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

class GetAllowanceList extends Content
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/GetAllowanceList';

    /**
     * 初始化查詢內容。
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'SearchType' => '0',
            'AllowanceNo' => '',
            'InvoiceNo' => '',
            'Date' => '',
        ];
    }

    /**
     * 設定查詢方式。
     *
     * @param string $type
     * @return self
     */
    public function setSearchType(string $type): self
    {
        if (!in_array($type, ['0', '1', '2'], true)) {
            throw new InvalidParameterException('SearchType 僅能為 0、1 或 2。');
        }

        $this->content['Data']['SearchType'] = $type;

        return $this;
    }

    /**
     * 設定折讓單號。
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
     * 設定發票號碼。
     *
     * @param string $invoiceNo
     * @return self
     */
    public function setInvoiceNo(string $invoiceNo): self
    {
        if (!preg_match('/^[A-Z]{2}[0-9]{8}$/', $invoiceNo)) {
            throw new InvalidParameterException('InvoiceNo 格式錯誤，需為 2 碼英文 + 8 碼數字。');
        }

        $this->content['Data']['InvoiceNo'] = strtoupper($invoiceNo);

        return $this;
    }

    /**
     * 設定查詢日期。
     *
     * @param string $date
     * @return self
     */
    public function setDate(string $date): self
    {
        $this->content['Data']['Date'] = $this->normalizeDate($date);

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

        $type = $this->content['Data']['SearchType'];

        if (!in_array($type, ['0', '1', '2'], true)) {
            throw new InvalidParameterException('SearchType 僅能為 0、1 或 2。');
        }

        if ($type === '0') {
            if (empty($this->content['Data']['AllowanceNo'])) {
                throw new InvalidParameterException('SearchType 為 0 時，AllowanceNo 為必填。');
            }
        } else {
            if (empty($this->content['Data']['InvoiceNo'])) {
                throw new InvalidParameterException('SearchType 為 1 或 2 時，InvoiceNo 為必填。');
            }

            if (empty($this->content['Data']['Date'])) {
                throw new InvalidParameterException('SearchType 為 1 或 2 時，Date 為必填。');
            }
        }
    }

    /**
     * 將日期正規化為 Y-m-d 格式。
     *
     * @param string $date
     * @return string
     */
    private function normalizeDate(string $date): string
    {
        $formats = ['Y-m-d', 'Y/m/d'];

        foreach ($formats as $format) {
            $dateTime = \DateTime::createFromFormat($format, $date);

            if ($dateTime && $dateTime->format($format) === $date) {
                return $dateTime->format('Y-m-d');
            }
        }

        throw new InvalidParameterException('Date 格式需為 yyyy-MM-dd 或 yyyy/MM/dd。');
    }
}

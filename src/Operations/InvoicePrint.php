<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Operations;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\InvoiceInterface;
use Exception;

class InvoicePrint extends Content
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/InvoicePrint';

    /**
     * 初始化內容。
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'InvoiceNo' => '',
            'InvoiceDate' => '',
            'PrintStyle' => 1,
            'IsReprintInvoice' => '',
            'IsShowingDetail' => null,
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
     * 設定發票日期。
     *
     * @param string $date
     * @return InvoiceInterface
     */
    public function setInvoiceDate(string $date): self
    {
        $this->content['Data']['InvoiceDate'] = $this->normalizeDate($date);

        return $this;
    }

    /**
     * 設定列印格式。
     *
     * @param int $style
     * @return InvoiceInterface
     */
    public function setPrintStyle(int $style): self
    {
        if (!in_array($style, [1, 2, 3, 4, 5], true)) {
            throw new Exception('PrintStyle 僅支援 1~5。');
        }

        $this->content['Data']['PrintStyle'] = $style;

        return $this;
    }

    /**
     * 設定是否標示補印。
     *
     * @param bool $reprint
     * @return InvoiceInterface
     */
    public function setReprint(bool $reprint): self
    {
        $this->content['Data']['IsReprintInvoice'] = $reprint ? 'Y' : '';

        return $this;
    }

    /**
     * 設定是否顯示明細。
     *
     * @param int $value
     * @return InvoiceInterface
     */
    public function setShowingDetail(int $value): self
    {
        if (!in_array($value, [1, 2], true)) {
            throw new Exception('IsShowingDetail 僅支援 1 或 2。');
        }

        $this->content['Data']['IsShowingDetail'] = $value;

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

        if (empty($this->content['Data']['InvoiceDate'])) {
            throw new Exception('InvoiceDate 不可為空。');
        }

        if (!in_array($this->content['Data']['PrintStyle'], [1, 2, 3, 4, 5], true)) {
            throw new Exception('PrintStyle 僅支援 1~5。');
        }

        if ($this->content['Data']['IsReprintInvoice'] !== '' && $this->content['Data']['IsReprintInvoice'] !== 'Y') {
            throw new Exception('IsReprintInvoice 僅能為空字串或 Y。');
        }

        if (
            $this->content['Data']['IsShowingDetail'] !== null
            && !in_array($this->content['Data']['IsShowingDetail'], [1, 2], true)
        ) {
            throw new Exception('IsShowingDetail 僅支援 1 或 2。');
        }
    }

    /**
     * 將日期正規化為 Y-m-d。
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

        throw new Exception('InvoiceDate 格式需為 yyyy-MM-dd 或 yyyy/MM/dd。');
    }
}


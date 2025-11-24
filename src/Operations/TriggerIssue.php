<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Operations;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\InvoiceInterface;
use Exception;

class TriggerIssue extends Content
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/TriggerIssue';

    /**
     * 初始化觸發開立資料。
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'Tsr' => '',
            'PayType' => '2',
        ];
    }

    /**
     * 設定交易單號。
     *
     * @param string $tsr
     * @return InvoiceInterface
     */
    public function setTsr(string $tsr): self
    {
        if ($tsr === '' || strlen($tsr) > 30) {
            throw new Exception('Tsr 長度需介於 1~30 字。');
        }

        $this->content['Data']['Tsr'] = $tsr;

        return $this;
    }

    /**
     * 設定交易類別。
     *
     * @param string $type
     * @return InvoiceInterface
     */
    public function setPayType(string $type): self
    {
        if ($type !== '2') {
            throw new Exception('PayType 僅支援 2。');
        }

        $this->content['Data']['PayType'] = $type;

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

        if (empty($this->content['Data']['Tsr'])) {
            throw new Exception('Tsr 不可為空。');
        }

        if ($this->content['Data']['PayType'] !== '2') {
            throw new Exception('PayType 僅支援 2。');
        }
    }
}

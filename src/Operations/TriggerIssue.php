<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Operations;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

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
     * @return self
     */
    public function setTsr(string $tsr): self
    {
        if ($tsr === '' || strlen($tsr) > 30) {
            throw new ValidationException('Tsr 長度需介於 1~30 字。');
        }

        $this->content['Data']['Tsr'] = $tsr;

        return $this;
    }

    /**
     * 設定交易類別。
     *
     * @param string $type
     * @return self
     */
    public function setPayType(string $type): self
    {
        if ($type !== '2') {
            throw new ValidationException('PayType 僅支援 2。');
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
            throw new ValidationException('Tsr 不可為空。');
        }

        if ($this->content['Data']['PayType'] !== '2') {
            throw new ValidationException('PayType 僅支援 2。');
        }
    }
}

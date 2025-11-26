<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Operations;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

class CancelDelayIssue extends Content
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/CancelDelayIssue';

    /**
     * 初始化取消延遲開立資料。
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'Tsr' => '',
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
    }
}

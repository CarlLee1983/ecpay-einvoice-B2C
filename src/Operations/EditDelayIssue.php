<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Operations;

use Exception;

class EditDelayIssue extends DelayIssue
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/EditDelayIssue';

    /**
     * 驗證內容。
     *
     * @return void
     */
    public function validation()
    {
        parent::validation();

        if (empty($this->content['Data']['Tsr'])) {
            throw new Exception('編輯延遲開立發票時必須帶入 Tsr。');
        }
    }
}

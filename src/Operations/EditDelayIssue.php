<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Operations;

use CarlLee\EcPayB2C\Exceptions\ValidationException;

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
            throw new ValidationException('編輯延遲開立發票時必須帶入 Tsr。');
        }
    }
}

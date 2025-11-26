<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Queries;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\InvoiceInterface;
use Exception;

class GetCompanyNameByTaxID extends Content
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/GetCompanyNameByTaxID';

    /**
     * 初始化查詢內容。
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'UnifiedBusinessNo' => '',
        ];
    }

    /**
     * 設定統一編號。
     *
     * @param string $taxId
     * @return InvoiceInterface
     */
    public function setUnifiedBusinessNo(string $taxId): self
    {
        $taxId = trim($taxId);

        $this->assertUnifiedBusinessNoFormat($taxId);

        $this->content['Data']['UnifiedBusinessNo'] = $taxId;

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

        if (empty($this->content['Data']['UnifiedBusinessNo'])) {
            throw new Exception('統一編號不可為空值。');
        }

        $this->assertUnifiedBusinessNoFormat($this->content['Data']['UnifiedBusinessNo']);
    }

    /**
     * 確認統編格式是否符合八碼數字。
     *
     * @param string $taxId
     * @return void
     */
    private function assertUnifiedBusinessNoFormat(string $taxId): void
    {
        if (strlen($taxId) !== 8 || !ctype_digit($taxId)) {
            throw new Exception('統一編號需為 8 碼阿拉伯數字。');
        }
    }
}

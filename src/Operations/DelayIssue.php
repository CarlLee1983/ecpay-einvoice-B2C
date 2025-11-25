<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Operations;

use Exception;

class DelayIssue extends Invoice
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/DelayIssue';

    /**
     * 延遲開立相關欄位的預設值。
     *
     * @return void
     */
    protected function initContent()
    {
        parent::initContent();

        $this->content['Data']['DelayFlag'] = '';
        $this->content['Data']['DelayDay'] = 0;
        $this->content['Data']['Tsr'] = '';
        $this->content['Data']['PayType'] = '';
        $this->content['Data']['PayAct'] = '';
    }

    /**
     * 設定延遲註記。
     *
     * @param string $flag
     * @return $this
     */
    public function setDelayFlag(string $flag): self
    {
        if (!in_array($flag, ['1', '2'], true)) {
            throw new Exception('DelayFlag 僅能為 1(延遲) 或 2(觸發)。');
        }

        $this->content['Data']['DelayFlag'] = $flag;

        return $this;
    }

    /**
     * 設定延遲天數。
     *
     * @param int $day
     * @return $this
     */
    public function setDelayDay(int $day): self
    {
        if ($day < 1 || $day > 15) {
            throw new Exception('DelayDay 必須介於 1 到 15 天。');
        }

        $this->content['Data']['DelayDay'] = $day;

        return $this;
    }

    /**
     * 設定交易單號 (TSR)。
     *
     * @param string $tsr
     * @return $this
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
     * 設定付款方式。
     *
     * @param string $type
     * @return $this
     */
    public function setPayType(string $type): self
    {
        if (!in_array($type, ['2'], true)) {
            throw new Exception('PayType 目前僅支援 2(綠界代收)。');
        }

        $this->content['Data']['PayType'] = $type;

        return $this;
    }

    /**
     * 設定付款帳號。
     *
     * @param string $account
     * @return $this
     */
    public function setPayAct(string $account): self
    {
        if ($account === '' || strlen($account) > 16) {
            throw new Exception('PayAct 長度需介於 1~16 字。');
        }

        $this->content['Data']['PayAct'] = $account;

        return $this;
    }

    /**
     * 驗證內容。
     *
     * @return void
     */
    public function validation()
    {
        parent::validation();

        $flag = $this->content['Data']['DelayFlag'];
        $day = $this->content['Data']['DelayDay'];

        if ($flag === '') {
            throw new Exception('DelayFlag 不可為空。');
        }

        if ($day < 1 || $day > 15) {
            throw new Exception('DelayDay 必須介於 1 到 15 天。');
        }

        if ($flag === '2') {
            if (empty($this->content['Data']['Tsr'])) {
                throw new Exception('觸發開立時 Tsr 為必填。');
            }

            if ($this->content['Data']['PayType'] !== '2') {
                throw new Exception('觸發開立僅支援 PayType = 2。');
            }

            if (empty($this->content['Data']['PayAct'])) {
                throw new Exception('觸發開立時 PayAct 為必填。');
            }
        }
    }
}

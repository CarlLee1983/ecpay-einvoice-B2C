<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Queries;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

class GetIssueList extends Content
{
    /**
     * API 路徑。
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/GetIssueList';

    /**
     * 初始化查詢內容。
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'BeginDate' => '',
            'EndDate' => '',
            'NumPerPage' => 50,
            'ShowingPage' => 1,
            'Format' => '1',
        ];
    }

    /**
     * 設定查詢起始日期。
     *
     * @param string $date
     * @return self
     */
    public function setBeginDate(string $date): self
    {
        $this->content['Data']['BeginDate'] = $this->normalizeDate($date);

        return $this;
    }

    /**
     * 設定查詢結束日期。
     *
     * @param string $date
     * @return self
     */
    public function setEndDate(string $date): self
    {
        $this->content['Data']['EndDate'] = $this->normalizeDate($date);

        return $this;
    }

    /**
     * 設定單頁顯示數量。
     *
     * @param int $number
     * @return self
     */
    public function setNumPerPage(int $number): self
    {
        $this->assertPerPageRange($number);
        $this->content['Data']['NumPerPage'] = $number;

        return $this;
    }

    /**
     * 設定顯示頁數。
     *
     * @param int $page
     * @return self
     */
    public function setShowingPage(int $page): self
    {
        if ($page < 1) {
            throw new InvalidParameterException('ShowingPage 必須大於等於 1。');
        }

        $this->content['Data']['ShowingPage'] = $page;

        return $this;
    }

    /**
     * 設定回傳格式。
     *
     * @param string $format
     * @return self
     */
    public function setFormat(string $format): self
    {
        if (!in_array($format, ['1', '2'], true)) {
            throw new InvalidParameterException('Format 僅支援 1(JSON) 或 2(CSV)。');
        }

        $this->content['Data']['Format'] = $format;

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

        if (empty($this->content['Data']['BeginDate'])) {
            throw new InvalidParameterException('BeginDate 不可為空。');
        }

        if (empty($this->content['Data']['EndDate'])) {
            throw new InvalidParameterException('EndDate 不可為空。');
        }

        $this->assertPerPageRange((int) $this->content['Data']['NumPerPage']);

        if ((int) $this->content['Data']['ShowingPage'] < 1) {
            throw new InvalidParameterException('ShowingPage 必須大於等於 1。');
        }

        if (!in_array($this->content['Data']['Format'], ['1', '2'], true)) {
            throw new InvalidParameterException('Format 僅支援 1(JSON) 或 2(CSV)。');
        }

        $begin = new \DateTime($this->content['Data']['BeginDate']);
        $end = new \DateTime($this->content['Data']['EndDate']);

        if ($begin > $end) {
            throw new InvalidParameterException('BeginDate 不得晚於 EndDate。');
        }
    }

    /**
     * 驗證單頁顯示範圍。
     *
     * @param int $number
     * @return void
     */
    private function assertPerPageRange(int $number): void
    {
        if ($number < 1 || $number > 200) {
            throw new InvalidParameterException('NumPerPage 必須介於 1 到 200。');
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

        throw new InvalidParameterException('日期格式需為 yyyy-MM-dd 或 yyyy/MM/dd。');
    }
}

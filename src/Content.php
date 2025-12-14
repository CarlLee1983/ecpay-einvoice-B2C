<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

use CarlLee\EcPay\Core\AbstractContent;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

/**
 * B2C 電子發票 Content 基礎類別。
 *
 * 繼承自 Core 的 AbstractContent，提供 B2C 特有的功能。
 */
abstract class Content extends AbstractContent implements InvoiceInterface
{
    /**
     * 設定關聯單號。
     *
     * @param string $relateNumber 關聯單號
     * @return $this
     * @throws ValidationException 當關聯單號過長時
     */
    public function setRelateNumber(string $relateNumber): self
    {
        if (strlen($relateNumber) > self::RELATE_NUMBER_MAX_LENGTH) {
            throw ValidationException::tooLong('RelateNumber', self::RELATE_NUMBER_MAX_LENGTH);
        }

        $this->content['Data']['RelateNumber'] = $relateNumber;

        return $this;
    }

    /**
     * 設定發票日期。
     *
     * @param string $date 日期（格式：yyyy-mm-dd）
     * @return $this
     * @throws ValidationException 當日期格式錯誤時
     */
    public function setInvoiceDate(string $date): self
    {
        $format = 'Y-m-d';
        $dateTime = \DateTime::createFromFormat($format, $date);

        if (!($dateTime && $dateTime->format($format) === $date)) {
            throw ValidationException::invalid('InvoiceDate', '格式必須為 yyyy-mm-dd');
        }

        $this->content['Data']['InvoiceDate'] = $date;

        return $this;
    }

    /**
     * 取得 Response 實例。
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return new Response();
    }

    /**
     * 驗證基礎參數。
     *
     * @param bool $requireCredentials 是否需要驗證金鑰
     * @throws ValidationException
     */
    #[\Override]
    protected function validatorBaseParam(bool $requireCredentials = false): void
    {
        if (empty($this->content['MerchantID']) || empty($this->content['Data']['MerchantID'])) {
            throw ValidationException::required('MerchantID');
        }

        if ($requireCredentials) {
            $this->validateCredentials();
        }
    }

    /**
     * 關聯單號最大長度。
     */
    public const int RELATE_NUMBER_MAX_LENGTH = 30;
}

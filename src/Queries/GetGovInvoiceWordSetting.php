<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Queries;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

class GetGovInvoiceWordSetting extends Content
{
    /**
     * API endpoint path.
     *
     * @var string
     */
    protected string $requestPath = '/B2CInvoice/GetGovInvoiceWordSetting';

    /**
     * Initialize request payload.
     *
     * @return void
     */
    protected function initContent(): void
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'InvoiceYear' => '',
        ];
    }

    /**
     * Set the invoice year (ROC year; Gregorian years are auto-converted).
     *
     * @param int|string $year
     * @return self
     */
    public function setInvoiceYear(int|string $year): self
    {
        $year = $this->normalizeInvoiceYear($year);
        $this->content['Data']['InvoiceYear'] = $year;

        return $this;
    }

    /**
     * Validate payload.
     *
     * @return void
     */
    protected function validation(): void
    {
        $this->validatorBaseParam();

        if (empty($this->content['Data']['InvoiceYear'])) {
            throw new InvalidParameterException('InvoiceYear cannot be empty.');
        }

        $this->assertInvoiceYearRange((int) $this->content['Data']['InvoiceYear']);
    }

    /**
     * Normalize the provided year into ROC format.
     *
     * @param int|string $year
     * @return string
     */
    private function normalizeInvoiceYear(int|string $year): string
    {
        if (is_int($year)) {
            $year = (string) $year;
        }

        $year = trim($year);

        if ($year === '') {
            throw new InvalidParameterException('InvoiceYear cannot be empty.');
        }

        if (!ctype_digit($year)) {
            throw new InvalidParameterException('InvoiceYear must be numeric.');
        }

        if (strlen($year) === 4) {
            $converted = (int) $year - 1911;

            if ($converted <= 0) {
                throw new InvalidParameterException('Gregorian year must be greater than 1911.');
            }

            $year = (string) $converted;
        }

        if (strlen($year) > 3) {
            throw new InvalidParameterException('InvoiceYear must be 3 digits in ROC format.');
        }

        $yearValue = (int) $year;

        if ($yearValue <= 0) {
            throw new InvalidParameterException('InvoiceYear must be a positive integer.');
        }

        $this->assertInvoiceYearRange($yearValue);

        return str_pad((string) $yearValue, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Ensure the invoice year falls within last, current, or next year.
     *
     * @param int $year
     * @return void
     */
    private function assertInvoiceYearRange(int $year): void
    {
        $currentRocYear = (int) date('Y') - 1911;
        $min = $currentRocYear - 1;
        $max = $currentRocYear + 1;

        if ($year < $min || $year > $max) {
            throw new InvalidParameterException('InvoiceYear can only target last, current, or next year.');
        }
    }
}

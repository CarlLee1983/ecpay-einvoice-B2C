<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Operations;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\InvoiceInterface;
use ecPay\eInvoice\Parameter\InvType;
use Exception;

class AddInvoiceWordSetting extends Content
{
    /**
     * Allowed suffixes for the starting invoice number.
     */
    private const START_SUFFIXES = ['00', '50'];

    /**
     * Allowed suffixes for the ending invoice number.
     */
    private const END_SUFFIXES = ['49', '99'];

    /**
     * API endpoint path.
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/AddInvoiceWordSetting';

    /**
     * Initialize request payload.
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'InvoiceTerm' => null,
            'InvoiceYear' => '',
            'InvType' => InvType::GENERAL,
            'InvoiceCategory' => '1',
            'ProductServiceId' => '',
            'InvoiceHeader' => '',
            'InvoiceStart' => '',
            'InvoiceEnd' => '',
        ];
    }

    /**
     * Set the invoice term.
     *
     * @param int $term
     * @return InvoiceInterface
     */
    public function setInvoiceTerm(int $term): self
    {
        $this->assertInvoiceTerm($term);
        $this->content['Data']['InvoiceTerm'] = $term;

        return $this;
    }

    /**
     * Set the invoice year (supports ROC or Gregorian input).
     *
     * @param int|string $year
     * @return InvoiceInterface
     */
    public function setInvoiceYear(int|string $year): self
    {
        $this->content['Data']['InvoiceYear'] = $this->normalizeInvoiceYear($year);

        return $this;
    }

    /**
     * Set the invoice type.
     *
     * @param string $type
     * @return InvoiceInterface
     */
    public function setInvType(string $type): self
    {
        if (!in_array($type, [InvType::GENERAL, InvType::SPECIAL], true)) {
            throw new Exception('InvType only supports 07 or 08.');
        }

        $this->content['Data']['InvType'] = $type;

        return $this;
    }

    /**
     * Set the product service identifier.
     *
     * @param string $productServiceId
     * @return InvoiceInterface
     */
    public function setProductServiceId(string $productServiceId): self
    {
        $productServiceId = trim($productServiceId);

        if ($productServiceId === '') {
            $this->content['Data']['ProductServiceId'] = '';

            return $this;
        }

        if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $productServiceId)) {
            throw new Exception('ProductServiceId must be 1-10 alphanumeric characters.');
        }

        $this->content['Data']['ProductServiceId'] = $productServiceId;

        return $this;
    }

    /**
     * Set the invoice track header.
     *
     * @param string $header
     * @return InvoiceInterface
     */
    public function setInvoiceHeader(string $header): self
    {
        $header = strtoupper(trim($header));

        if (!preg_match('/^[A-Z]{2}$/', $header)) {
            throw new Exception('InvoiceHeader must contain exactly two letters.');
        }

        $this->content['Data']['InvoiceHeader'] = $header;

        return $this;
    }

    /**
     * Set the starting invoice number.
     *
     * @param string|int $number
     * @return InvoiceInterface
     */
    public function setInvoiceStart(string|int $number): self
    {
        $this->content['Data']['InvoiceStart'] = $this->normalizeInvoiceNumber(
            $number,
            self::START_SUFFIXES,
            'InvoiceStart'
        );

        return $this;
    }

    /**
     * Set the ending invoice number.
     *
     * @param string|int $number
     * @return InvoiceInterface
     */
    public function setInvoiceEnd(string|int $number): self
    {
        $this->content['Data']['InvoiceEnd'] = $this->normalizeInvoiceNumber(
            $number,
            self::END_SUFFIXES,
            'InvoiceEnd'
        );

        return $this;
    }

    /**
     * Validate payload.
     *
     * @return void
     */
    public function validation()
    {
        $this->validatorBaseParam();

        if (!is_int($this->content['Data']['InvoiceTerm'])) {
            throw new Exception('InvoiceTerm cannot be empty.');
        }

        $this->assertInvoiceTerm($this->content['Data']['InvoiceTerm']);

        if (empty($this->content['Data']['InvoiceYear'])) {
            throw new Exception('InvoiceYear cannot be empty.');
        }

        $invoiceYear = (int) $this->content['Data']['InvoiceYear'];
        $this->assertInvoiceYearRange($invoiceYear);
        $this->assertInvoiceTermNotPast($this->content['Data']['InvoiceTerm'], $invoiceYear);

        if (empty($this->content['Data']['InvoiceHeader'])) {
            throw new Exception('InvoiceHeader cannot be empty.');
        }

        if (empty($this->content['Data']['InvoiceStart']) || empty($this->content['Data']['InvoiceEnd'])) {
            throw new Exception('InvoiceStart and InvoiceEnd cannot be empty.');
        }

        $start = (int) $this->content['Data']['InvoiceStart'];
        $end = (int) $this->content['Data']['InvoiceEnd'];

        if ($start >= $end) {
            throw new Exception('InvoiceStart must be less than InvoiceEnd.');
        }

        if ((($end - $start) + 1) % 50 !== 0) {
            throw new Exception('Invoice number range must be aligned to 50-number batches.');
        }

        if (!in_array($this->content['Data']['InvType'], [InvType::GENERAL, InvType::SPECIAL], true)) {
            throw new Exception('InvType only supports 07 or 08.');
        }

        if ($this->content['Data']['InvoiceCategory'] !== '1') {
            throw new Exception('InvoiceCategory must be 1.');
        }

        if (
            !empty($this->content['Data']['ProductServiceId']) &&
            !preg_match('/^[A-Za-z0-9]{1,10}$/', $this->content['Data']['ProductServiceId'])
        ) {
            throw new Exception('ProductServiceId must be 1-10 alphanumeric characters.');
        }
    }

    /**
     * Ensure term value is within allowed range.
     *
     * @param int $term
     * @return void
     */
    private function assertInvoiceTerm(int $term): void
    {
        if ($term < 1 || $term > 6) {
            throw new Exception('InvoiceTerm must be between 1 and 6.');
        }
    }

    /**
     * Ensure the requested term is not earlier than the current term.
     *
     * @param int $term
     * @param int $year
     * @return void
     */
    private function assertInvoiceTermNotPast(int $term, int $year): void
    {
        $currentYear = $this->getCurrentRocYear();

        if ($year === $currentYear) {
            $currentTerm = $this->getCurrentInvoiceTerm();

            if ($term < $currentTerm) {
                throw new Exception('InvoiceTerm cannot be earlier than the current term.');
            }
        }
    }

    /**
     * Normalize the invoice year into ROC format.
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
            throw new Exception('InvoiceYear cannot be empty.');
        }

        if (!ctype_digit($year)) {
            throw new Exception('InvoiceYear must be numeric.');
        }

        if (strlen($year) === 4) {
            $converted = (int) $year - 1911;

            if ($converted <= 0) {
                throw new Exception('Gregorian year must be greater than 1911.');
            }

            $year = (string) $converted;
        }

        if (strlen($year) > 3) {
            throw new Exception('InvoiceYear must be 3 digits in ROC format.');
        }

        $yearValue = (int) $year;
        $this->assertInvoiceYearRange($yearValue);

        return str_pad((string) $yearValue, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Ensure invoice year can only target current or next year.
     *
     * @param int $year
     * @return void
     */
    private function assertInvoiceYearRange(int $year): void
    {
        $current = $this->getCurrentRocYear();
        $allowed = [$current, $current + 1];

        if (!in_array($year, $allowed, true)) {
            throw new Exception('InvoiceYear must be either the current or next year.');
        }
    }

    /**
     * Normalize invoice numbers (8 digits only) and validate suffixes.
     *
     * @param string|int $number
     * @param array $allowedSuffixes
     * @param string $field
     * @return string
     */
    private function normalizeInvoiceNumber(string|int $number, array $allowedSuffixes, string $field): string
    {
        $number = trim((string) $number);

        if ($number === '') {
            throw new Exception(sprintf('%s cannot be empty.', $field));
        }

        if (ctype_digit($number) && strlen($number) < 8) {
            $number = str_pad($number, 8, '0', STR_PAD_LEFT);
        }

        if (!preg_match('/^\d{8}$/', $number)) {
            throw new Exception(sprintf('%s must be an 8-digit number.', $field));
        }

        $suffix = substr($number, -2);

        if (!in_array($suffix, $allowedSuffixes, true)) {
            $message = $field === 'InvoiceStart'
                ? 'InvoiceStart must end with 00 or 50.'
                : 'InvoiceEnd must end with 49 or 99.';

            throw new Exception($message);
        }

        return $number;
    }

    /**
     * Get the current ROC year.
     *
     * @return int
     */
    private function getCurrentRocYear(): int
    {
        return (int) date('Y') - 1911;
    }

    /**
     * Get the current invoice term.
     *
     * @return int
     */
    private function getCurrentInvoiceTerm(): int
    {
        return (int) ceil(((int) date('n')) / 2);
    }
}


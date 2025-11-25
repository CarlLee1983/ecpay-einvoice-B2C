<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Queries;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\InvoiceInterface;
use CarlLee\EcPayB2C\Parameter\InvType;
use Exception;

class GetInvoiceWordSetting extends Content
{
    /**
     * API endpoint path.
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/GetInvoiceWordSetting';

    /**
     * Initialize request payload.
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'InvoiceYear' => '',
            'InvoiceTerm' => 0,
            'UseStatus' => 0,
            'InvoiceCategory' => 1,
            'InvType' => '',
            'ProductServiceId' => '',
            'InvoiceHeader' => '',
        ];
    }

    /**
     * Set the invoice year (supports input in Gregorian or ROC format).
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
     * Set the invoice term (0 represents all terms).
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
     * Set the track usage status (0 represents all statuses).
     *
     * @param int $status
     * @return InvoiceInterface
     */
    public function setUseStatus(int $status): self
    {
        $this->assertUseStatus($status);
        $this->content['Data']['UseStatus'] = $status;

        return $this;
    }

    /**
     * Set the invoice category (only 1 is allowed).
     *
     * @param int $category
     * @return InvoiceInterface
     */
    public function setInvoiceCategory(int $category): self
    {
        if ($category !== 1) {
            throw new Exception('InvoiceCategory must be 1.');
        }

        $this->content['Data']['InvoiceCategory'] = $category;

        return $this;
    }

    /**
     * Set the invoice track type.
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
     * Set the track header.
     *
     * @param string $header
     * @return InvoiceInterface
     */
    public function setInvoiceHeader(string $header): self
    {
        $header = strtoupper(trim($header));

        if ($header === '') {
            $this->content['Data']['InvoiceHeader'] = '';

            return $this;
        }

        if (!preg_match('/^[A-Z]{2}$/', $header)) {
            throw new Exception('InvoiceHeader must contain exactly two letters.');
        }

        $this->content['Data']['InvoiceHeader'] = $header;

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

        if (empty($this->content['Data']['InvoiceYear'])) {
            throw new Exception('InvoiceYear cannot be empty.');
        }

        $this->assertInvoiceTerm($this->content['Data']['InvoiceTerm']);
        $this->assertUseStatus($this->content['Data']['UseStatus']);

        if ($this->content['Data']['InvoiceCategory'] !== 1) {
            throw new Exception('InvoiceCategory must be 1.');
        }

        if (
            !empty($this->content['Data']['InvType']) &&
            !in_array($this->content['Data']['InvType'], [InvType::GENERAL, InvType::SPECIAL], true)
        ) {
            throw new Exception('InvType only supports 07 or 08.');
        }

        if (
            !empty($this->content['Data']['ProductServiceId']) &&
            !preg_match('/^[A-Za-z0-9]{1,10}$/', $this->content['Data']['ProductServiceId'])
        ) {
            throw new Exception('ProductServiceId must be 1-10 alphanumeric characters.');
        }

        if (
            !empty($this->content['Data']['InvoiceHeader']) &&
            !preg_match('/^[A-Z]{2}$/', $this->content['Data']['InvoiceHeader'])
        ) {
            throw new Exception('InvoiceHeader must contain exactly two letters.');
        }
    }

    /**
     * Ensure the term is within the allowed range.
     *
     * @param int $term
     * @return void
     */
    private function assertInvoiceTerm(int $term): void
    {
        if ($term < 0 || $term > 6) {
            throw new Exception('InvoiceTerm must be between 0 and 6.');
        }
    }

    /**
     * Ensure the usage status is within the allowed range.
     *
     * @param int $status
     * @return void
     */
    private function assertUseStatus(int $status): void
    {
        if ($status < 0 || $status > 6) {
            throw new Exception('UseStatus must be between 0 and 6.');
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

        $current = (int) date('Y') - 1911;
        $min = $current - 1;
        $max = $current + 1;

        if ($yearValue < $min || $yearValue > $max) {
            throw new Exception('InvoiceYear can only target last, current, or next year.');
        }

        return str_pad((string) $yearValue, 3, '0', STR_PAD_LEFT);
    }
}

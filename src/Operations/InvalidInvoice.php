<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Operations;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

class InvalidInvoice extends Content
{
    /**
     * The request path.
     *
     * @var string
     */
    protected string $requestPath = '/B2CInvoice/Invalid';

    /**
     * Initialize invoice content.
     *
     * @return void
     */
    protected function initContent(): void
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'RelateNumber' => '',
            'InvoiceNo' => '',
            'InvoiceDate' => '',
            'Reason' => '',
        ];
    }

    /**
     * Setting the invoice no.
     *
     * @param string $invoiceNo
     * @return self
     */
    public function setInvoiceNo(string $invoiceNo): self
    {
        if (strlen($invoiceNo) != 10) {
            throw new ValidationException('The invoice no length should be 10.');
        }

        $this->content['Data']['InvoiceNo'] = $invoiceNo;

        return $this;
    }

    /**
     * Setting the invoice date.
     *
     * @param \DateTimeInterface|string $invoiceDate
     * @return $this
     */
    public function setInvoiceDate($invoiceDate): self
    {
        if ($invoiceDate instanceof \DateTimeInterface) {
            $invoiceDate = $invoiceDate->format('Y-m-d');
        }

        $this->content['Data']['InvoiceDate'] = $invoiceDate;

        return $this;
    }

    /**
     * Setting invoice invalid reason.
     *
     * @param string $reason
     * @return self
     */
    public function setReason(string $reason): self
    {
        $this->content['Data']['Reason'] = $reason;

        return $this;
    }

    /**
     * Validation content.
     *
     * @return void
     */
    protected function validation(): void
    {
        $this->validatorBaseParam();

        if (empty($this->content['Data']['InvoiceNo'])) {
            throw new ValidationException('The invoice no is empty.');
        }

        if (empty($this->content['Data']['InvoiceDate'])) {
            throw new ValidationException('The invoice date is empty.');
        }

        if (empty($this->content['Data']['Reason'])) {
            throw new ValidationException('The invoice invalid reason is empty.');
        }
    }
}

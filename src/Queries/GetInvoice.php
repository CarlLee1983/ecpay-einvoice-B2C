<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Queries;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

class GetInvoice extends Content
{
    /**
     * The request path.
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/GetIssue';

    /**
     * Initialize invoice content.
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'RelateNumber' => '',
            'InvoiceNo' => '',
            'InvoiceDate' => '',
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
     * Validation content.
     *
     * @return void
     */
    public function validation()
    {
        $this->validatorBaseParam();

        if (empty($this->content['Data']['InvoiceNo'])) {
            throw new ValidationException('The invoice no is empty.');
        }

        if (empty($this->content['Data']['InvoiceDate'])) {
            throw new ValidationException('The invoice date is empty.');
        }
    }
}

<?php

namespace ecPay\eInvoice\Operations;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\InvoiceInterface;
use Exception;

class AllowanceInvalid extends Content
{
    /**
     * The request path.
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/AllowanceInvalid';

    /**
     * Initialize invoice content.
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'InvoiceNo' => '',
            'AllowanceNo' => '',
            'Reason' => '',
        ];
    }

    /**
     * Setting the invoice no.
     *
     * @param string $invoiceNo
     * @return InvoiceInterface
     */
    public function setInvoiceNo(string $invoiceNo): self
    {
        if (strlen($invoiceNo) != 10) {
            throw new Exception('The invoice no length should be 10.');
        }

        $this->content['Data']['InvoiceNo'] = $invoiceNo;

        return $this;
    }

    /**
     * Setting invoice allowance no.
     *
     * @param string $number
     * @return InvoiceInterface
     */
    public function setAllowanceNo(string $number): self
    {
        if (strlen($number) != 16) {
            throw new Exception('The invoice allowance no length should be 16.');
        }

        $this->content['Data']['AllowanceNo'] = $number;

        return $this;
    }

    /**
     * Setting invoice invalid reason.
     *
     * @param string $reason
     * @return InvoiceInterface
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
    public function validation()
    {
        $this->validatorBaseParam();

        if (empty($this->content['Data']['InvoiceNo'])) {
            throw new Exception('The invoice no is empty.');
        }

        if (empty($this->content['Data']['AllowanceNo'])) {
            throw new Exception('The invoice allowance no is empty.');
        }

        if (empty($this->content['Data']['Reason'])) {
            throw new Exception('The invoice invalid reason is empty.');
        }
    }
}

<?php

namespace ecPay\eInvoice;

interface InvoiceInterface
{
    /**
     * Get the invoice content.
     *
     * @return array
     */
    public function getContent(): array;

    /**
     * Validation content.
     *
     * @return void
     */
    public function validation();
}

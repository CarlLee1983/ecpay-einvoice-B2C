<?php

namespace ecPay\eInvoice;

use Exception;

class CheckLoveCode extends Content
{
    /**
     * The request path.
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/CheckLoveCode';

    /**
     * Initialize invoice content.
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'LoveCode' => '',
        ];
    }

    /**
     * Setting donate code.
     *
     * @param string $code
     * @return InvoiceInterface
     */
    public function setLoveCode(string $code): self
    {
        if (strlen($code) < 3 || strlen($code) > 7) {
            throw new Exception('The donate code length must be between 3 and 8 characters.');
        }

        $this->content['Data']['LoveCode'] = $code;

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

        if (empty($this->content['Data']['LoveCode'])) {
            throw new Exception('Phone barcode is empty.');
        }
    }
}

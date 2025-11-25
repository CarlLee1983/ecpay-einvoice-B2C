<?php

namespace CarlLee\EcPayB2C\Queries;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\InvoiceInterface;
use Exception;

class CheckBarcode extends Content
{
    /**
     * The request path.
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/CheckBarcode';

    /**
     * Initialize invoice content.
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'BarCode' => '',
        ];
    }

    /**
     * Setting barcode.
     *
     * @param string $code
     * @return self
     */
    public function setBarcode(string $code): self
    {
        $barcode = strtoupper($code);

        $this->assertBarcodeFormat($barcode);

        $this->content['Data']['BarCode'] = $barcode;

        return $this;
    }

    /**
     * Validation content.
     */
    public function validation()
    {
        $this->validatorBaseParam();

        if (empty($this->content['Data']['BarCode'])) {
            throw new Exception('Phone barcode is empty.');
        }

        $this->assertBarcodeFormat($this->content['Data']['BarCode']);
    }

    /**
     * Validate barcode format constraint.
     *
     * @param string $code
     */
    private function assertBarcodeFormat(string $code): void
    {
        if (!preg_match('/^\/[0-9A-Z+\-.]{7}$/', $code)) {
            throw new Exception('Phone barcode format invalid.');
        }
    }
}

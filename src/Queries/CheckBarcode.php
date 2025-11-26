<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Queries;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;

class CheckBarcode extends Content
{
    /**
     * The request path.
     *
     * @var string
     */
    protected string $requestPath = '/B2CInvoice/CheckBarcode';

    /**
     * Initialize invoice content.
     */
    protected function initContent(): void
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
    protected function validation(): void
    {
        $this->validatorBaseParam();

        if (empty($this->content['Data']['BarCode'])) {
            throw new InvalidParameterException('Phone barcode is empty.');
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
            throw new InvalidParameterException('Phone barcode format invalid.');
        }
    }
}

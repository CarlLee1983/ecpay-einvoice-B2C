<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Queries;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\InvoiceInterface;
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
            throw new Exception('The donate code length must be between 3 and 7 characters.');
        }

        $this->content['Data']['LoveCode'] = $code;

        return $this;
    }

    /**
     * Validation content.
     */
    public function validation()
    {
        $this->validatorBaseParam();

        if (empty($this->content['Data']['LoveCode'])) {
            throw new Exception('Love code is empty.');
        }
    }
}

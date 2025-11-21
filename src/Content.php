<?php

declare(strict_types=1);

namespace ecPay\eInvoice;

use Exception;

abstract class Content implements InvoiceInterface
{
    use AES;

    /**
     * ECPay invoice api version.
     */
    const VERSION = '3.0.0';

    /**
     * The relate number max length.
     */
    const RELATE_NUMBER_MAX_LENGTH = 30;

    /**
     * The RqID random string length.
     */
    const RQID_RANDOM_LENGTH = 5;

    /**
     * The request server.
     *
     * @var string
     */
    protected $requestServer = '';

    /**
     * The request path.
     *
     * @var string
     */
    protected $requestPath = '';

    /**
     * The content merchant id.
     *
     * @var string
     */
    protected $merchantID = '';

    /**
     * Hash key;
     *
     * @var string
     */
    protected $hashKey = '';

    /**
     * Hash IV.
     *
     * @var string
     */
    protected $hashIV = '';

    /**
     * The Response instance.
     *
     * @var Response
     */
    public $response;

    /**
     * The content.
     *
     * @var array
     */
    protected $content = [];

    /**
     * __construct
     *
     * @param string $merchantId
     * @param string $hashKey
     * @param string $hashIV
     */
    public function __construct(string $merchantId = '', string $hashKey = '', string $hashIV = '')
    {
        $this->response = new Response();
        // Server is no longer needed here, as it's handled by EcPayClient

        $this->setMerchantID($merchantId);
        $this->setHashKey($hashKey);
        $this->setHashIV($hashIV);

        $this->content = [
            'MerchantID' => $this->merchantID,
            'RqHeader' => [
                'Timestamp' => time(),
                'RqID' => $this->getRqID(),
                'Revision' => self::VERSION,
            ],
        ];

        $this->initContent();
    }

    /**
     * Initialize invoice content.
     */
    protected function initContent()
    {
        $this->content = [];
    }

    /**
     * Get the request path.
     *
     * @return string
     */
    public function getRequestPath(): string
    {
        return $this->requestPath;
    }

    /**
     * Set the content merchant id.
     *
     * @param string $id
     * @return Content
     */
    public function setMerchantID(string $id): self
    {
        $this->merchantID = $id;

        return $this;
    }

    /**
     * Set hash key.
     *
     * @param string $key
     * @return $this
     */
    public function setHashKey($key): self
    {
        $this->hashKey = $key;

        return $this;
    }

    /**
     * Set hash iv.
     *
     * @param string $iv
     * @return $this
     */
    public function setHashIV($iv): self
    {
        $this->hashIV = $iv;

        return $this;
    }

    /**
     * Get the RqID.
     *
     * @return string
     */
    protected function getRqID(): string
    {
        list($usec, $sec) = explode(' ', microtime());
        $usec = str_replace('.', '', $usec);

        return $sec . $this->randomString(self::RQID_RANDOM_LENGTH) . $usec . $this->randomString(self::RQID_RANDOM_LENGTH);
    }

    /**
     * Get random string.
     *
     * @param int $length
     * @return string
     */
    private function randomString($length = 32): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if (!is_int($length) || $length < 0) {
            return '';
        }

        $charactersLength = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[random_int(0, $charactersLength)];
        }

        return $string;
    }

    /**
     * Trans php urlencode to .net encode.
     *
     * @param string $param
     * @return string
     */
    protected function transUrlencode($param): string
    {
        $search = ['%2d', '%5f', '%2e', '%21', '%2a', '%28', '%29'];
        $replace = ['-', '_', '.', '!', '*', '(', ')'];

        return str_replace($search, $replace, $param);
    }

    /**
     * Setting Relate number.
     *
     * @param string $relateNumber
     * @return InvoiceInterface
     */
    public function setRelateNumber(string $relateNumber): InvoiceInterface
    {
        if (strlen($relateNumber) > self::RELATE_NUMBER_MAX_LENGTH) {
            throw new Exception('The invoice RelateNumber length over ' . self::RELATE_NUMBER_MAX_LENGTH . '.');
        }

        $this->content['Data']['RelateNumber'] = $relateNumber;

        return $this;
    }

    /**
     * Setting invoice data.
     *
     * @param string $date
     * @return InvoiceInterface
     */
    public function setInvoiceDate(string $date): InvoiceInterface
    {
        $format = 'Y-m-d';
        $dateTime = \DateTime::createFromFormat($format, $date);

        if (!($dateTime && $dateTime->format($format) === $date)) {
            throw new Exception('The invoice date format is invalid.');
        }

        $this->content['Data']['InvoiceDate'] = $date;

        return $this;
    }

    /**
     * Get content.
     *
     * @return array
     */
    public function getContent(): array
    {
        $this->validation();

        $content = $this->content;
        $content['Data'] = json_encode($content['Data']);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('The invoice data format is invalid.');
        }

        $content['Data'] = urlencode($content['Data']);
        $content['Data'] = $this->transUrlencode($content['Data']);
        $content['Data'] = $this->encrypt($content['Data']);

        return $content;
    }

    /**
     * Validator base parameters.
     *
     * @throws Exception
     */
    protected function validatorBaseParam()
    {
        if (empty($this->content['MerchantID']) || empty($this->content['Data']['MerchantID'])) {
            throw new Exception('MerchantID is empty.');
        }

        if (empty($this->hashKey)) {
            throw new Exception('HashKey is empty.');
        }

        if (empty($this->hashIV)) {
            throw new Exception('HashIV is empty.');
        }
    }

    /**
     * Get response.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}

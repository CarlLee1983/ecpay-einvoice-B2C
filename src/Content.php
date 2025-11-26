<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

use CarlLee\EcPayB2C\Contracts\CommandInterface;
use CarlLee\EcPayB2C\DTO\RqHeaderDto;
use CarlLee\EcPayB2C\Exceptions\ConfigurationException;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Infrastructure\CipherService;
use CarlLee\EcPayB2C\Infrastructure\PayloadEncoder;

abstract class Content implements InvoiceInterface, CommandInterface
{
    use AES;

    /**
     * The relate number max length.
     */
    public const int RELATE_NUMBER_MAX_LENGTH = 30;

    /**
     * The RqID random string length.
     */
    public const int RQID_RANDOM_LENGTH = 5;

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
     * @var RqHeaderDto
     */
    protected RqHeaderDto $rqHeader;

    /**
     * @var PayloadEncoder|null
     */
    protected $payloadEncoder;

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

        $this->rqHeader = new RqHeaderDto();

        $this->content = [
            'MerchantID' => $this->merchantID,
            'RqHeader' => $this->rqHeader->toPayload(),
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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

        return $sec
            . $this->randomString(self::RQID_RANDOM_LENGTH)
            . $usec
            . $this->randomString(self::RQID_RANDOM_LENGTH);
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
     * @return $this
     */
    public function setRelateNumber(string $relateNumber): self
    {
        if (strlen($relateNumber) > self::RELATE_NUMBER_MAX_LENGTH) {
            throw new InvalidParameterException('The invoice RelateNumber length over ' . self::RELATE_NUMBER_MAX_LENGTH . '.');
        }

        $this->content['Data']['RelateNumber'] = $relateNumber;

        return $this;
    }

    /**
     * Setting invoice data.
     *
     * @param string $date
     * @return $this
     */
    public function setInvoiceDate(string $date): self
    {
        $format = 'Y-m-d';
        $dateTime = \DateTime::createFromFormat($format, $date);

        if (!($dateTime && $dateTime->format($format) === $date)) {
            throw new InvalidParameterException('The invoice date format is invalid.');
        }

        $this->content['Data']['InvoiceDate'] = $date;

        return $this;
    }

    /**
     * 取得 RqHeader DTO。
     */
    public function getRqHeader(): RqHeaderDto
    {
        return $this->rqHeader;
    }

    /**
     * 設定 RqHeader DTO。
     */
    public function setRqHeader(RqHeaderDto $rqHeader): self
    {
        $this->rqHeader = $rqHeader;
        $this->syncRqHeader();

        return $this;
    }

    /**
     * 設定自訂的 PayloadEncoder，以支援自外部注入的傳輸層。
     */
    public function setPayloadEncoder(PayloadEncoder $payloadEncoder): self
    {
        $this->payloadEncoder = $payloadEncoder;

        return $this;
    }

    /**
     * 取得純領域欄位，不包含加密處理。
     */
    #[\Override]
    public function getPayload(): array
    {
        $this->validation();
        $this->syncRqHeader();

        return $this->content;
    }

    /**
     * 既有 getContent 仍保留，改為委派給 PayloadEncoder。
     *
     * @return array
     */
    public function getContent(): array
    {
        $payload = $this->getPayload();
        $encoder = $this->getPayloadEncoder();

        return $encoder->encodePayload($payload);
    }

    /**
     * Validator base parameters.
     *
     * @throws ConfigurationException
     */
    protected function validatorBaseParam(bool $requireCredentials = false)
    {
        if (empty($this->content['MerchantID']) || empty($this->content['Data']['MerchantID'])) {
            throw new ConfigurationException('MerchantID is empty.');
        }

        if ($requireCredentials) {
            if (empty($this->hashKey)) {
                throw new ConfigurationException('HashKey is empty.');
            }

            if (empty($this->hashIV)) {
                throw new ConfigurationException('HashIV is empty.');
            }
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

    /**
     * 產生預設的 PayloadEncoder，提供相容性使用。
     */
    protected function buildPayloadEncoder(): PayloadEncoder
    {
        $this->validatorBaseParam(true);

        return new PayloadEncoder(
            new CipherService($this->hashKey, $this->hashIV)
        );
    }

    /**
     * 取得當前命令可用的 PayloadEncoder。
     */
    #[\Override]
    public function getPayloadEncoder(): PayloadEncoder
    {
        return $this->payloadEncoder ?: $this->buildPayloadEncoder();
    }

    /**
     * 同步 RqHeader 至內容陣列。
     */
    protected function syncRqHeader(): void
    {
        $this->content['RqHeader'] = $this->rqHeader->toPayload();
    }
}

<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Operations;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\DTO\InvoiceItemDto;
use CarlLee\EcPayB2C\DTO\ItemCollection;
use CarlLee\EcPayB2C\InvoiceValidator;
use CarlLee\EcPayB2C\Parameter\CarrierType;
use CarlLee\EcPayB2C\Parameter\ClearanceMark;
use CarlLee\EcPayB2C\Parameter\Donation;
use CarlLee\EcPayB2C\Parameter\InvType;
use CarlLee\EcPayB2C\Parameter\PrintMark;
use CarlLee\EcPayB2C\Parameter\TaxType;
use CarlLee\EcPayB2C\Parameter\VatType;
use Exception;

class Invoice extends Content
{
    /**
     * The request path.
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/Issue';

    /**
     * The invoice tax type.
     *
     * @var string
     */
    protected $taxType = TaxType::DUTIABLE->value;

    /**
     * The invoice content.
     *
     * @var array
     */
    protected $content = [];

    /**
     * @var ItemCollection
     */
    private ItemCollection $items;

    public function __construct(string $merchantId = '', string $hashKey = '', string $hashIV = '')
    {
        $this->items = new ItemCollection();

        parent::__construct($merchantId, $hashKey, $hashIV);
    }

    /**
     * Initialize invoice content.
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'RelateNumber' => '',
            'CustomerID' => '',
            'CustomerIdentifier' => '',
            'CustomerName' => '',
            'CustomerAddr' => '',
            'CustomerPhone' => '',
            'CustomerEmail' => '',
            'ClearanceMark' => '',
            'Print' => PrintMark::NO->value,
            'Donation' => Donation::NO->value,
            'LoveCode' => '',
            'CarrierType' => CarrierType::NONE->value,
            'CarrierNum' => '',
            'TaxType' => $this->taxType,
            'SalesAmount' => 0,
            'InvoiceRemark' => '',
            'Items' => [],
            'InvType' => InvType::GENERAL->value,
            'vat' => VatType::YES->value,
        ];
    }

    /**
     * Set the invoice customer identifier.
     *
     * @param string $identifier
     * @return $this
     */
    public function setCustomerIdentifier(string $identifier): self
    {
        $this->content['Data']['CustomerIdentifier'] = $identifier;

        return $this;
    }

    /**
     * Set the invoice customer name.
     *
     * @param string $name
     * @return $this
     */
    public function setCustomerName(string $name): self
    {
        $this->content['Data']['CustomerName'] = $name;

        return $this;
    }

    /**
     * Set the invoice customer address.
     *
     * @param string $address
     * @return $this
     */
    public function setCustomerAddr(string $address): self
    {
        $this->content['Data']['CustomerAddr'] = $address;

        return $this;
    }

    /**
     * Set the invoice customer Phone.
     *
     * @param string $phone
     * @return $this
     */
    public function setCustomerPhone(string $phone): self
    {
        $this->content['Data']['CustomerPhone'] = $phone;

        return $this;
    }

    /**
     * Set the invoice customer email.
     *
     * @param string $email
     * @return $this
     */
    public function setCustomerEmail(string $email): self
    {
        $this->content['Data']['CustomerEmail'] = $email;

        return $this;
    }

    /**
     * Set the invoice clearance mark.
     *
     * @param string $mark
     * @return $this
     */
    public function setClearanceMark(string $mark): self
    {
        if (!in_array($mark, [ClearanceMark::YES->value, ClearanceMark::NO->value])) {
            throw new Exception('Invoice clearance mark format is invalid.');
        }

        $this->content['Data']['ClearanceMark'] = $mark;

        return $this;
    }

    /**
     * Set the invoice print mark.
     *
     * @param string $mark
     * @return $this
     */
    public function setPrintMark(string $mark): self
    {
        if ($mark != PrintMark::YES->value && $mark != PrintMark::NO->value) {
            throw new Exception('Invoice print mark format is wrong.');
        }

        $this->content['Data']['Print'] = (string) $mark;

        return $this;
    }

    /**
     * Set the invoice donation.
     *
     * @param string $donation
     * @return $this
     */
    public function setDonation(string $donation): self
    {
        if (!in_array($donation, [Donation::YES->value, Donation::NO->value])) {
            throw new Exception('Invoice donation format is wrong.');
        }

        $this->content['Data']['Donation'] = (string) $donation;

        return $this;
    }

    /**
     * Set the invoice love code.
     *
     * @param string $code
     * @return $this
     */
    public function setLoveCode(string $code): self
    {
        $counter = strlen($code);

        if ($counter > 7 || $counter < 3) {
            throw new Exception('Invoice love code is wrong.');
        }

        $this->content['Data']['LoveCode'] = (string) $code;

        return $this;
    }

    /**
     * Set the invoice carrier type.
     *
     * @param string $type
     * @return self
     */
    public function setCarrierType(string $type): self
    {
        $carrierType = [
            CarrierType::NONE->value,
            CarrierType::MEMBER->value,
            CarrierType::CITIZEN->value,
            CarrierType::CELLPHONE->value,
        ];

        if (!in_array($type, $carrierType)) {
            throw new Exception('Invoice carrier type format is wrong.');
        }

        $this->content['Data']['CarrierType'] = (string) $type;

        return $this;
    }

    /**
     * Set the invoice carrier number.
     *
     * @param string $number
     * @return self
     */
    public function setCarrierNum(string $number): self
    {
        $this->content['Data']['CarrierNum'] = $number;

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
     * Set the invoice tax type.
     *
     * @param string $type
     * @return self
     */
    public function setTaxType(string $type): self
    {
        $taxType = [
            TaxType::DUTIABLE->value,
            TaxType::ZERO->value,
            TaxType::FREE->value,
            TaxType::MIX->value,
        ];

        if (!in_array($type, $taxType)) {
            throw new Exception('Invoice tax type format is invalid.');
        }

        $this->taxType = $type;
        $this->content['Data']['TaxType'] = $type;

        return $this;
    }

    /**
     * Set the invoice special tax type.
     *
     * @param string $type
     * @return self
     */
    public function setSpecialTaxType(string $type): self
    {
        $this->content['Data']['SpecialTaxType'] = $type;

        return $this;
    }

    /**
     * Set the invoice sales amount.
     *
     * @param float|int $amount
     * @return self
     */
    public function setSalesAmount($amount): self
    {
        if ($amount <= 0) {
            throw new Exception('Invoice sales amount is invalid.');
        }

        $this->content['Data']['SalesAmount'] = $amount;

        return $this;
    }

    /**
     * @param array<int,InvoiceItemDto|array<string,mixed>> $items
     */
    public function setItems(array $items): self
    {
        $collection = new ItemCollection();

        foreach ($items as $item) {
            if (is_array($item)) {
                $item = InvoiceItemDto::fromArray($item);
            }

            if (!$item instanceof InvoiceItemDto) {
                throw new Exception('Each invoice item must be an InvoiceItemDto or array definition.');
            }

            $collection->add($item);
        }

        $this->items = $collection;

        return $this;
    }

    /**
     * Validation content.
     *
     * @throws Exception
     */
    public function validation()
    {
        $this->validatorBaseParam();
        $this->content['Data']['Items'] = $this->buildItemsPayload();

        // Sync SalesAmount with Items sum
        $amount = round($this->items->sumAmount());

        if (!empty($this->content['Data']['SalesAmount']) && $this->content['Data']['SalesAmount'] != $amount) {
            throw new Exception('The calculated sales amount is not equal to the set sales amount.');
        }

        $this->content['Data']['SalesAmount'] = $amount;

        // Delegate validation to InvoiceValidator
        InvoiceValidator::validate($this->content['Data'], $this->items);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function buildItemsPayload(): array
    {
        $index = 1;
        return $this->items->mapPayload(function (array $payload) use (&$index): array {
            if (!isset($payload['ItemTaxType'])) {
                $payload['ItemTaxType'] = $this->taxType;
            }

            $payload['ItemSeq'] = $index++;

            return $payload;
        });
    }
}

<?php

declare(strict_types=1);

namespace ecPay\eInvoice;

use ecPay\eInvoice\Parameter\CarrierType;
use ecPay\eInvoice\Parameter\ClearanceMark;
use ecPay\eInvoice\Parameter\Donation;
use ecPay\eInvoice\Parameter\InvType;
use ecPay\eInvoice\Parameter\PrintMark;
use ecPay\eInvoice\Parameter\TaxType;
use ecPay\eInvoice\Parameter\VatType;
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
    protected $taxType = TaxType::DUTIABLE;

    /**
     * The invoice content.
     *
     * @var array
     */
    protected $content = [];

    /**
     * The invoice items.
     *
     * @var array
     */
    protected $items = [];

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
            'CustomerID' => '',
            'CustomerIdentifier' => '',
            'CustomerName' => '',
            'CustomerAddr' => '',
            'CustomerPhone' => '',
            'CustomerEmail' => '',
            'ClearanceMark' => '',
            'Print' => PrintMark::NO,
            'Donation' => Donation::NO,
            'LoveCode' => '',
            'CarrierType' => CarrierType::NONE,
            'CarrierNum' => '',
            'TaxType' => $this->taxType,
            'SalesAmount' => 0,
            'InvoiceRemark' => '',
            'Items' => [],
            'InvType' => InvType::GENERAL,
            'vat' => VatType::YES,
        ];
    }

    /**
     * Set the invoice customer identifier.
     *
     * @param string $identifier
     * @return InvoiceInterface
     */
    public function setCustomerIdentifier(string $identifier): InvoiceInterface
    {
        $this->content['Data']['CustomerIdentifier'] = $identifier;

        return $this;
    }

    /**
     * Set the invoice customer name.
     *
     * @param string $name
     * @return InvoiceInterface
     */
    public function setCustomerName(string $name): InvoiceInterface
    {
        $this->content['Data']['CustomerName'] = $name;

        return $this;
    }

    /**
     * Set the invoice customer address.
     *
     * @param string $address
     * @return InvoiceInterface
     */
    public function setCustomerAddr(string $address): InvoiceInterface
    {
        $this->content['Data']['CustomerAddr'] = $address;

        return $this;
    }

    /**
     * Set the invoice customer Phone.
     *
     * @param string $phone
     * @return InvoiceInterface
     */
    public function setCustomerPhone(string $phone): InvoiceInterface
    {
        $this->content['Data']['CustomerPhone'] = $phone;

        return $this;
    }

    /**
     * Set the invoice customer email.
     *
     * @param string $email
     * @return InvoiceInterface
     */
    public function setCustomerEmail(string $email): InvoiceInterface
    {
        $this->content['Data']['CustomerEmail'] = $email;

        return $this;
    }

    /**
     * Set the invoice clearance mark.
     *
     * @param string $mark
     * @return InvoiceInterface
     */
    public function setClearanceMark(string $mark): InvoiceInterface
    {
        if (!in_array($mark, [ClearanceMark::YES, ClearanceMark::NO])) {
            throw new Exception('Invoice clearance mark format is invalid.');
        }

        $this->content['Data']['ClearanceMark'] = $mark;

        return $this;
    }

    /**
     * Set the invoice print mark.
     *
     * @param string $mark
     * @return InvoiceInterface
     */
    public function setPrintMark(string $mark): InvoiceInterface
    {
        if ($mark != PrintMark::YES && $mark != PrintMark::NO) {
            throw new Exception('Invoice print mark format is wrong.');
        }

        $this->content['Data']['Print'] = (string) $mark;

        return $this;
    }

    /**
     * Set the invoice donation.
     *
     * @param string $donation
     * @return InvoiceInterface
     */
    public function setDonation(string $donation): InvoiceInterface
    {
        if (!in_array($donation, [Donation::YES, Donation::NO])) {
            throw new Exception('Invoice donation format is wrong.');
        }

        $this->content['Data']['Donation'] = (string) $donation;

        return $this;
    }

    /**
     * Set the invoice love code.
     *
     * @param string $code
     * @return InvoiceInterface
     */
    public function setLoveCode(string $code): InvoiceInterface
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
     * @return InvoiceInterface
     */
    public function setCarrierType(string $type): InvoiceInterface
    {
        $carrierType = [
            CarrierType::NONE,
            CarrierType::MEMBER,
            CarrierType::CITIZEN,
            CarrierType::CELLPHONE,
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
     * @return InvoiceInterface
     */
    public function setCarrierNum(string $number): InvoiceInterface
    {
        $this->content['Data']['CarrierNum'] = $number;

        return $this;
    }

    /**
     * Set the invoice tax type.
     *
     * @param string $type
     * @return InvoiceInterface
     */
    public function setTaxType(string $type): InvoiceInterface
    {
        $taxType = [
            TaxType::DUTIABLE,
            TaxType::ZERO,
            TaxType::FREE,
            TaxType::MIX,
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
     * @return InvoiceInterface
     */
    public function setSpecialTaxType(string $type): InvoiceInterface
    {
        $this->content['Data']['SpecialTaxType'] = $type;

        return $this;
    }

    /**
     * Set the invoice sales amount.
     *
     * @param float|int $amount
     * @return InvoiceInterface
     */
    public function setSalesAmount($amount): InvoiceInterface
    {
        if ($amount <= 0) {
            throw new Exception('Invoice sales amount is invalid.');
        }

        $this->content['Data']['SalesAmount'] = $amount;

        return $this;
    }

    /**
     * Set the invoice item.
     *
     * @param array $items
     * @return InvoiceInterface
     */
    public function setItems(array $items): InvoiceInterface
    {
        $this->content['Data']['SalesAmount'] = 0;
        $this->items = [];
        $fields = ['name', 'quantity', 'unit', 'price'];

        foreach ($items as $item) {
            foreach ($fields as $name) {
                if (!isset($item[$name])) {
                    throw new Exception('Items field' . $name . ' not exists.');
                }
            }

            $this->items[] = [
                'ItemName' => $item['name'],
                'ItemCount' => (float) $item['quantity'],
                'ItemWord' => $item['unit'],
                'ItemPrice' => (float) $item['price'],
                'ItemTaxType' => $this->taxType,
                'ItemAmount' => $item['quantity'] * $item['price'],
            ];
        }

        return $this;
    }

    /**
     * Get the invoice content.
     *
     * @return array
     */
    public function getContent(): array
    {
        $this->content['Data']['Items'] = $this->items;

        return parent::getContent();
    }

    /**
     * Validation content.
     *
     * @return void
     * @throws Exception
     */
    public function validation()
    {
        $this->validatorBaseParam();

        // Sync SalesAmount with Items sum
        $amount = 0;
        foreach ($this->items as $item) {
            $amount += $item['ItemAmount'];
        }

        if (!empty($this->content['Data']['SalesAmount']) && $this->content['Data']['SalesAmount'] != $amount) {
            throw new Exception('The calculated sales amount is not equal to the set sales amount.');
        }

        $this->content['Data']['SalesAmount'] = $amount;

        // Delegate validation to InvoiceValidator
        InvoiceValidator::validate($this->content['Data'], $this->items);
    }
}

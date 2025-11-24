<?php

namespace ecPay\eInvoice\Operations;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\DTO\AllowanceItemDto;
use ecPay\eInvoice\DTO\ItemCollection;
use ecPay\eInvoice\InvoiceInterface;
use ecPay\eInvoice\Parameter\AllowanceNotifyType;
use Exception;

class AllowanceInvoice extends Content
{
    /**
     * The request path.
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/Allowance';

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
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'InvoiceNo' => '',
            'InvoiceDate' => '',
            'AllowanceNotify' => AllowanceNotifyType::NONE,
            'CustomerName' => '',
            'NotifyMail' => '',
            'NotifyPhone' => '',
            'setAllowanceAmount' => 0,
            'items' => [],
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
     * Setting allownace notify.
     *
     * @param string $type
     * @return InvoiceInterface
     */
    public function setAllowanceNotify(string $type): self
    {
        $allownaceType = [
            AllowanceNotifyType::SMS,
            AllowanceNotifyType::EMAIL,
            AllowanceNotifyType::ALL,
            AllowanceNotifyType::NONE,
        ];

        if (!in_array($type, $allownaceType)) {
            throw new Exception('The invoice allowance notify type is invalid.');
        }

        $this->content['Data']['AllowanceNotify'] = $type;

        return $this;
    }

    /**
     * Setting customer name.
     *
     * @param string $name
     * @return InvoiceInterface
     */
    public function setCustomerName(string $name): self
    {
        if (empty($name)) {
            throw new Exception('Customer name is empty.');
        }

        $this->content['Data']['CustomerName'] = $name;

        return $this;
    }

    /**
     * Setting allownace notify email.
     *
     * @param string $email
     * @return InvoiceInterface
     */
    public function setNotifyMail(string $email): self
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        if (strlen($email) > 100) {
            throw new Exception('Email length must be less than 100 characters.');
        }

        $this->content['Data']['NotifyMail'] = $email;

        return $this;
    }

    /**
     * Setting allowance notify phone number.
     *
     * @param string $number
     * @return InvoiceInterface
     */
    public function setNotifyPhone(string $number): self
    {
        if (strlen($number) > 20) {
            throw new Exception('Phone number length must be less than 21 characters.');
        }

        $this->content['Data']['NotifyPhone'] = $number;

        return $this;
    }

    /**
     * Setting the invoice allowance amount.
     *
     * @param integer $amount
     * @return InvoiceInterface
     */
    public function setAllowanceAmount(int $amount): self
    {
        $this->content['Data']['AllowanceAmount'] = $amount;

        return $this;
    }

    /**
     * @param array<int,AllowanceItemDto|array<string,mixed>> $items
     */
    public function setItems(array $items): self
    {
        $collection = new ItemCollection();

        foreach ($items as $item) {
            if (is_array($item)) {
                $item = AllowanceItemDto::fromArray($item);
            }

            if (!$item instanceof AllowanceItemDto) {
                throw new Exception('Each allowance item must be an AllowanceItemDto or array definition.');
            }

            $collection->add($item);
        }

        $this->items = $collection;
        $this->content['Data']['AllowanceAmount'] = (int) $this->items->sumAmount();

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
        $this->content['Data']['Items'] = $this->items->toArray();

        if (empty($this->content['Data']['InvoiceNo'])) {
            throw new Exception('The invoice no is empty.');
        }

        if (empty($this->content['Data']['InvoiceDate'])) {
            throw new Exception('The invoice date is empty.');
        }

        if (
            $this->content['Data']['AllowanceNotify'] == AllowanceNotifyType::EMAIL
            && empty($this->content['Data']['NotifyMail'])
        ) {
            throw new Exception('The allowance notify is mail, email should be setting.');
        }

        if (
            $this->content['Data']['AllowanceNotify'] == AllowanceNotifyType::SMS
            && empty($this->content['Data']['NotifyPhone'])
        ) {
            throw new Exception('The allowance notify is SMS, phone number should be setting.');
        }

        if ($this->content['Data']['AllowanceAmount'] <= 0) {
            throw new Exception('The allowance amount should be greater than 0.');
        }

        if ($this->items->isEmpty()) {
            throw new Exception('The items is empty.');
        }
    }
}

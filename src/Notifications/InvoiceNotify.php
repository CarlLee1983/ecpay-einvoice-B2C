<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Notifications;

use CarlLee\EcPayB2C\Content;
use CarlLee\EcPayB2C\Exceptions\InvalidParameterException;
use CarlLee\EcPayB2C\Exceptions\ValidationException;
use CarlLee\EcPayB2C\Parameter\InvoiceTagType;
use CarlLee\EcPayB2C\Parameter\NotifiedType;
use CarlLee\EcPayB2C\Parameter\NotifyType;

class InvoiceNotify extends Content
{
    /**
     * The invoice no length.
     */
    public const INVOICE_NO_LENGTH = 10;

    /**
     * The allowance no length.
     */
    public const ALLOWANCE_NO_LENGTH = 16;

    /**
     * The phone max length.
     */
    public const PHONE_MAX_LENGTH = 20;

    /**
     * The email max length.
     */
    public const EMAIL_MAX_LENGTH = 80;

    /**
     * The request path.
     *
     * @var string
     */
    protected string $requestPath = '/B2CInvoice/InvoiceNotify';

    /**
     * Initialize invoice content.
     */
    protected function initContent(): void
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'InvoiceNo' => '',
            'AllowanceNo' => '',
            'Phone' => '',
            'NotifyMail' => '',
            'Notify' => '',
            'InvoiceTag' => '',
            'Notified' => '',
        ];
    }

    /**
     * Setting the invoice no.
     *
     * @param string $invoiceNo
     * @return self
     */
    public function setInvoiceNo(string $invoiceNo): self
    {
        if (strlen($invoiceNo) != self::INVOICE_NO_LENGTH) {
            throw new InvalidParameterException('The invoice no length should be ' . self::INVOICE_NO_LENGTH . '.');
        }

        $this->content['Data']['InvoiceNo'] = $invoiceNo;

        return $this;
    }

    /**
     * Setting invoice allowance no.
     *
     * @param string $number
     * @return self
     */
    public function setAllowanceNo(string $number): self
    {
        if (strlen($number) != self::ALLOWANCE_NO_LENGTH) {
            throw new InvalidParameterException('The invoice allowance no length should be ' . self::ALLOWANCE_NO_LENGTH . '.');
        }

        $this->content['Data']['AllowanceNo'] = $number;

        return $this;
    }

    /**
     * Set notify phone number.
     *
     * @param string $number
     * @return self
     */
    public function setPhone(string $number): self
    {
        if (strlen($number) > self::PHONE_MAX_LENGTH) {
            $max = self::PHONE_MAX_LENGTH + 1;
            throw new InvalidParameterException('Notify phone number should be less than ' . $max . ' characters');
        }

        $this->content['Data']['Phone'] = $number;

        return $this;
    }

    /**
     * Setting allowance notify email.
     *
     * @param string $email
     * @return self
     */
    public function setNotifyMail(string $email): self
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidParameterException('Invalid email format');
        }

        if (strlen($email) > self::EMAIL_MAX_LENGTH) {
            throw new InvalidParameterException('Email length must be less than ' . self::EMAIL_MAX_LENGTH . ' characters.');
        }

        $this->content['Data']['NotifyMail'] = $email;

        return $this;
    }

    /**
     * Setting the invoice notify type.
     *
     * @param string $type
     * @return self
     */
    public function setNotify(string $type): self
    {
        $notifyType = [
            NotifyType::SMS->value,
            NotifyType::EMAIL->value,
            NotifyType::ALL->value,
        ];

        if (!in_array($type, $notifyType)) {
            throw new InvalidParameterException('Notify type format is invalid.');
        }

        $this->content['Data']['Notify'] = $type;

        return $this;
    }

    /**
     * Setting the invoice notify tag.
     *
     * @param string $tag
     * @return self
     */
    public function setInvoiceTag(string $tag): self
    {
        $invoiceTag = [
            InvoiceTagType::INVOICE->value,
            InvoiceTagType::INVOICE_VOID->value,
            InvoiceTagType::ALLOWANCE->value,
            InvoiceTagType::ALLOWANCE_VOID->value,
            InvoiceTagType::INVOICE_WINNING->value,
        ];

        if (!in_array($tag, $invoiceTag)) {
            throw new InvalidParameterException('The invoice notify tag is invalid.');
        }

        $this->content['Data']['InvoiceTag'] = $tag;

        return $this;
    }

    /**
     * Setting Notify target.
     *
     * @param string $target
     * @return self
     */
    public function setNotified(string $target): self
    {
        $targetList = [
            NotifiedType::CUSTOMER->value,
            NotifiedType::VENDOR->value,
            NotifiedType::ALL->value,
        ];

        if (!in_array($target, $targetList)) {
            throw new InvalidParameterException('Notify target is invalid.');
        }

        $this->content['Data']['Notified'] = $target;

        return $this;
    }

    /**
     * Validation content.
     *
     * @throws Exception
     */
    protected function validation(): void
    {
        $this->validatorBaseParam();
        $data = $this->content['Data'];

        if (empty($data['InvoiceNo'])) {
            throw new InvalidParameterException('The invoice no is empty.');
        }

        if (
            in_array($data['InvoiceTag'], [
                InvoiceTagType::ALLOWANCE->value,
                InvoiceTagType::ALLOWANCE_VOID->value,
            ])
        ) {
            if (empty($data['AllowanceNo'])) {
                throw new InvalidParameterException('Invoice tag type is allowed or allowed invalid, `AllowanceNo` should be set.');
            }
        }

        if (empty($data['Phone']) && empty($data['NotifyMail'])) {
            throw new InvalidParameterException('Phone number or mail should be set.');
        }

        if (empty($data['Notify'])) {
            throw new InvalidParameterException('Notify is empty.');
        }

        if (empty($data['InvoiceTag'])) {
            throw new InvalidParameterException('Invoice tag is empty.');
        }

        if (empty($data['Notified'])) {
            throw new InvalidParameterException('Notified is empty.');
        }
    }
}

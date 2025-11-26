<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C;

use CarlLee\EcPayB2C\DTO\ItemCollection;
use CarlLee\EcPayB2C\Exceptions\ValidationException;
use CarlLee\EcPayB2C\Parameter\CarrierType;
use CarlLee\EcPayB2C\Parameter\Donation;
use CarlLee\EcPayB2C\Parameter\PrintMark;
use CarlLee\EcPayB2C\Parameter\TaxType;

class InvoiceValidator
{
    /**
     * The carrier citizen length.
     */
    public const CARRIER_CITIZEN_LENGTH = 16;

    /**
     * The carrier cellphone length.
     */
    public const CARRIER_CELLPHONE_LENGTH = 8;

    /**
     * Validate invoice data.
     *
     * @param array $data
     * @param ItemCollection $items
     * @throws ValidationException
     */
    public static function validate(array $data, ItemCollection $items)
    {
        self::validateBasicParams($data);
        self::validateCustomer($data);
        self::validateDonation($data);
        self::validateCarrier($data);
        self::validateItems($items);
    }

    /**
     * Validate basic parameters.
     *
     * @param array $data
     * @throws ValidationException
     */
    private static function validateBasicParams(array $data)
    {
        if (empty($data['RelateNumber'])) {
            throw new ValidationException('The invoice RelateNumber is empty.');
        }

        if ($data['TaxType'] == TaxType::ZERO->value) {
            if (empty($data['ClearanceMark'])) {
                throw new ValidationException('Invoice is duty free, clearance mark can not be empty.');
            }
        }
    }

    /**
     * Validate customer information and print mark.
     *
     * @param array $data
     * @throws ValidationException
     */
    private static function validateCustomer(array $data)
    {
        if ($data['Print'] == PrintMark::YES->value) {
            if (empty($data['CustomerName']) || empty($data['CustomerAddr'])) {
                throw new ValidationException('Because print mark is yes. Customer name and address can not be empty.');
            }
        }

        if (empty($data['CustomerPhone']) && empty($data['CustomerEmail'])) {
            throw new ValidationException('You should be settings either of customer phone and email.');
        }

        if (!empty($data['CustomerIdentifier'])) {
            if ($data['Print'] == PrintMark::NO->value) {
                throw new ValidationException('Because customer identifier not empty, print mark must be Yes');
            }

            if ($data['Donation'] == Donation::YES->value) {
                throw new ValidationException('Customer identifier not empty, donation can not be yes.');
            }
        }
    }

    /**
     * Validate donation logic.
     *
     * @param array $data
     * @throws ValidationException
     */
    private static function validateDonation(array $data)
    {
        if ($data['Donation'] == Donation::YES->value) {
            if (empty($data['LoveCode'])) {
                throw new ValidationException('Donation is yes, love code required.');
            }

            if ($data['Print'] == PrintMark::YES->value) {
                throw new ValidationException('Donation is yes, invoice can not be print.');
            }
        }
    }

    /**
     * Validate carrier type and number.
     *
     * @param array $data
     * @throws ValidationException
     */
    private static function validateCarrier(array $data)
    {
        if ($data['CarrierType'] == CarrierType::NONE->value) {
            if ($data['CarrierNum'] != '') {
                throw new ValidationException('Invoice carrier type is empty, carrier number must be empty.');
            }
        } else {
            if ($data['Print'] == PrintMark::YES->value) {
                throw new ValidationException('Carrier type is not empty, invoice can not be print.');
            }

            if ($data['CarrierType'] == CarrierType::MEMBER->value && $data['CarrierNum'] != '') {
                throw new ValidationException('Invoice carrier type is member, carrier number must be empty.');
            }

            $isCitizen = $data['CarrierType'] == CarrierType::CITIZEN->value;
            if ($isCitizen && strlen($data['CarrierNum']) != self::CARRIER_CITIZEN_LENGTH) {
                $len = self::CARRIER_CITIZEN_LENGTH;
                throw new ValidationException("Invoice carrier type is citizen, carrier number length must be {$len}.");
            }

            $isCellphone = $data['CarrierType'] == CarrierType::CELLPHONE->value;
            if ($isCellphone && strlen($data['CarrierNum']) != self::CARRIER_CELLPHONE_LENGTH) {
                $len = self::CARRIER_CELLPHONE_LENGTH;
                throw new ValidationException("Invoice carrier type is Cellphone, carrier number length must be {$len}.");
            }
        }
    }

    /**
     * Validate items.
     *
     * @param ItemCollection $items
     * @throws ValidationException
     */
    private static function validateItems(ItemCollection $items)
    {
        if ($items->isEmpty()) {
            throw new ValidationException('Invoice data items is Empty.');
        }
    }
}

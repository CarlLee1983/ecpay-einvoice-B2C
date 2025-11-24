<?php

declare(strict_types=1);

namespace ecPay\eInvoice;

use ecPay\eInvoice\DTO\ItemCollection;
use ecPay\eInvoice\Parameter\CarrierType;
use ecPay\eInvoice\Parameter\Donation;
use ecPay\eInvoice\Parameter\PrintMark;
use ecPay\eInvoice\Parameter\TaxType;
use Exception;

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
     * @throws Exception
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
     * @throws Exception
     */
    private static function validateBasicParams(array $data)
    {
        if (empty($data['RelateNumber'])) {
            throw new Exception('The invoice RelateNumber is empty.');
        }

        if ($data['TaxType'] == TaxType::ZERO) {
            if (empty($data['ClearanceMark'])) {
                throw new Exception('Invoice is duty free, clearance mark can not be empty.');
            }
        }
    }

    /**
     * Validate customer information and print mark.
     *
     * @param array $data
     * @throws Exception
     */
    private static function validateCustomer(array $data)
    {
        if ($data['Print'] == PrintMark::YES) {
            if (empty($data['CustomerName']) || empty($data['CustomerAddr'])) {
                throw new Exception('Because print mark is yes. Customer name and address can not be empty.');
            }
        }

        if (empty($data['CustomerPhone']) && empty($data['CustomerEmail'])) {
            throw new Exception('You should be settings either of customer phone and email.');
        }

        if (!empty($data['CustomerIdentifier'])) {
            if ($data['Print'] == PrintMark::NO) {
                throw new Exception('Because customer identifier not empty, print mark must be Yes');
            }

            if ($data['Donation'] == Donation::YES) {
                throw new Exception('Customer identifier not empty, donation can not be yes.');
            }
        }
    }

    /**
     * Validate donation logic.
     *
     * @param array $data
     * @throws Exception
     */
    private static function validateDonation(array $data)
    {
        if ($data['Donation'] == Donation::YES) {
            if (empty($data['LoveCode'])) {
                throw new Exception('Donation is yes, love code required.');
            }

            if ($data['Print'] == PrintMark::YES) {
                throw new Exception('Donation is yes, invoice can not be print.');
            }
        }
    }

    /**
     * Validate carrier type and number.
     *
     * @param array $data
     * @throws Exception
     */
    private static function validateCarrier(array $data)
    {
        if ($data['CarrierType'] == CarrierType::NONE) {
            if ($data['CarrierNum'] != '') {
                throw new Exception('Invoice carrier type is empty, carrier number must be empty.');
            }
        } else {
            if ($data['Print'] == PrintMark::YES) {
                throw new Exception('Carrier type is not empty, invoice can not be print.');
            }

            if ($data['CarrierType'] == CarrierType::MEMBER && $data['CarrierNum'] != '') {
                throw new Exception('Invoice carrier type is member, carrier number must be empty.');
            }

            if ($data['CarrierType'] == CarrierType::CITIZEN && strlen($data['CarrierNum']) != self::CARRIER_CITIZEN_LENGTH) {
                throw new Exception('Invoice carrier type is citizen, carrier number length must be ' . self::CARRIER_CITIZEN_LENGTH . '.');
            }

            if ($data['CarrierType'] == CarrierType::CELLPHONE && strlen($data['CarrierNum']) != self::CARRIER_CELLPHONE_LENGTH) {
                throw new Exception('Invoice carrier type is Cellphone, carrier number length must be ' . self::CARRIER_CELLPHONE_LENGTH . '.');
            }
        }
    }

    /**
     * Validate items.
     *
     * @param array $items
     * @throws Exception
     */
    private static function validateItems(ItemCollection $items)
    {
        if ($items->isEmpty()) {
            throw new Exception('Invoice data items is Empty.');
        }
    }
}

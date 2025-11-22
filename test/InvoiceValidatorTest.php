<?php

use ecPay\eInvoice\InvoiceValidator;
use ecPay\eInvoice\Parameter\CarrierType;
use ecPay\eInvoice\Parameter\Donation;
use ecPay\eInvoice\Parameter\PrintMark;
use ecPay\eInvoice\Parameter\TaxType;
use PHPUnit\Framework\TestCase;

class InvoiceValidatorTest extends TestCase
{
    /**
     * 測試基本參數驗證 - RelateNumber 為空
     */
    public function testValidateEmptyRelateNumber()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The invoice RelateNumber is empty.');

        $data = [
            'RelateNumber' => '',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::MEMBER,
            'CarrierNum' => '',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試零稅率需要清關標記
     */
    public function testValidateZeroTaxWithoutClearanceMark()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invoice is duty free, clearance mark can not be empty.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::ZERO,
            'ClearanceMark' => '',
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::MEMBER,
            'CarrierNum' => '',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試列印發票需要客戶名稱和地址
     */
    public function testValidatePrintMarkYesWithoutCustomerInfo()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Because print mark is yes. Customer name and address can not be empty.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::YES,
            'CustomerName' => '',
            'CustomerAddr' => '',
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::NONE,
            'CarrierNum' => '',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試必須設定電話或 Email
     */
    public function testValidateNoPhoneAndEmail()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('You should be settings either of customer phone and email.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::MEMBER,
            'CarrierNum' => '',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試統編不為空時，列印標記必須為 YES
     */
    public function testValidateCustomerIdentifierWithPrintMarkNo()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Because customer identifier not empty, print mark must be Yes');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerName' => '測試公司',
            'CustomerAddr' => '測試地址',
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '12345678',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::NONE,
            'CarrierNum' => '',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試統編不為空時，不能捐贈
     */
    public function testValidateCustomerIdentifierWithDonation()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Customer identifier not empty, donation can not be yes.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::YES,
            'CustomerName' => '測試公司',
            'CustomerAddr' => '測試地址',
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '12345678',
            'Donation' => Donation::YES,
            'LoveCode' => '123456',
            'CarrierType' => CarrierType::NONE,
            'CarrierNum' => '',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試捐贈時需要愛心碼
     */
    public function testValidateDonationWithoutLoveCode()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Donation is yes, love code required.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::YES,
            'LoveCode' => '',
            'CarrierType' => CarrierType::NONE,
            'CarrierNum' => '',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試捐贈時不能列印
     */
    public function testValidateDonationWithPrintMark()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Donation is yes, invoice can not be print.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::YES,
            'CustomerName' => '測試公司',
            'CustomerAddr' => '測試地址',
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::YES,
            'LoveCode' => '123456',
            'CarrierType' => CarrierType::NONE,
            'CarrierNum' => '',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試無載具時載具號碼必須為空
     */
    public function testValidateCarrierTypeNoneWithCarrierNum()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invoice carrier type is empty, carrier number must be empty.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::NONE,
            'CarrierNum' => '1234567890',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試有載具時不能列印
     */
    public function testValidateCarrierWithPrintMark()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Carrier type is not empty, invoice can not be print.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::YES,
            'CustomerName' => '測試公司',
            'CustomerAddr' => '測試地址',
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::MEMBER,
            'CarrierNum' => '',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試會員載具號碼必須為空
     */
    public function testValidateCarrierTypeMemberWithCarrierNum()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invoice carrier type is member, carrier number must be empty.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::MEMBER,
            'CarrierNum' => '1234567890',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試自然人憑證載具號碼長度必須為 16
     */
    public function testValidateCarrierTypeCitizenInvalidLength()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invoice carrier type is citizen, carrier number length must be 16.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::CITIZEN,
            'CarrierNum' => '123456789',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試手機條碼載具號碼長度必須為 8
     */
    public function testValidateCarrierTypeCellphoneInvalidLength()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invoice carrier type is Cellphone, carrier number length must be 8.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::CELLPHONE,
            'CarrierNum' => '12345',
        ];

        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試商品項目不能為空
     */
    public function testValidateEmptyItems()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invoice data items is Empty.');

        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::MEMBER,
            'CarrierNum' => '',
        ];

        InvoiceValidator::validate($data, []);
    }

    /**
     * 測試成功的驗證 - 會員載具
     */
    public function testValidateSuccessWithMemberCarrier()
    {
        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::MEMBER,
            'CarrierNum' => '',
        ];

        $this->expectNotToPerformAssertions();
        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試成功的驗證 - 手機條碼載具
     */
    public function testValidateSuccessWithCellphoneCarrier()
    {
        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::CELLPHONE,
            'CarrierNum' => '/YC+RROR',
        ];

        $this->expectNotToPerformAssertions();
        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試成功的驗證 - 自然人憑證載具
     */
    public function testValidateSuccessWithCitizenCarrier()
    {
        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::CITIZEN,
            'CarrierNum' => '1234567890123456',
        ];

        $this->expectNotToPerformAssertions();
        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試成功的驗證 - 列印發票（有統編）
     */
    public function testValidateSuccessWithPrintAndIdentifier()
    {
        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::YES,
            'CustomerName' => '測試公司',
            'CustomerAddr' => '測試地址',
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '12345678',
            'Donation' => Donation::NO,
            'CarrierType' => CarrierType::NONE,
            'CarrierNum' => '',
        ];

        $this->expectNotToPerformAssertions();
        InvoiceValidator::validate($data, [['name' => 'test']]);
    }

    /**
     * 測試成功的驗證 - 捐贈發票
     */
    public function testValidateSuccessWithDonation()
    {
        $data = [
            'RelateNumber' => 'TEST123',
            'TaxType' => TaxType::DUTIABLE,
            'Print' => PrintMark::NO,
            'CustomerPhone' => '0912345678',
            'CustomerEmail' => '',
            'CustomerIdentifier' => '',
            'Donation' => Donation::YES,
            'LoveCode' => '123456',
            'CarrierType' => CarrierType::NONE,
            'CarrierNum' => '',
        ];

        $this->expectNotToPerformAssertions();
        InvoiceValidator::validate($data, [['name' => 'test']]);
    }
}


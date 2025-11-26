<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Tests\Integration;

use CarlLee\EcPay\Core\Contracts\PayloadEncoderInterface;
use CarlLee\EcPay\Core\DTO\ItemCollection;
use CarlLee\EcPay\Core\DTO\RqHeaderDto;
use CarlLee\EcPay\Core\Exceptions\EncryptionException;
use CarlLee\EcPay\Core\Exceptions\ValidationException;
use CarlLee\EcPay\Core\Infrastructure\CipherService;
use CarlLee\EcPay\Core\Infrastructure\PayloadEncoder;
use CarlLee\EcPayB2C\DTO\InvoiceItemDto;
use CarlLee\EcPayB2C\DTO\AllowanceItemDto;
use CarlLee\EcPayB2C\Operations\Invoice;
use PHPUnit\Framework\TestCase;

/**
 * Core 套件整合測試。
 *
 * 驗證 ecpay-core 的各項功能在 B2C 套件中正常運作。
 */
class CoreIntegrationTest extends TestCase
{
    private const HASH_KEY = 'ejCk326UnaZWKisg';
    private const HASH_IV = 'q9jcZX8Ib9LM8wYk';
    private const MERCHANT_ID = '2000132';

    // ========================================
    // CipherService 測試
    // ========================================

    /** @test */
    public function cipher_service_can_encrypt_and_decrypt_english(): void
    {
        $cipher = new CipherService(self::HASH_KEY, self::HASH_IV);
        $original = '{"test":"data","number":123}';

        $encrypted = $cipher->encrypt($original);
        $decrypted = $cipher->decrypt($encrypted);

        $this->assertEquals($original, $decrypted);
        $this->assertNotEquals($original, $encrypted);
    }

    /** @test */
    public function cipher_service_can_encrypt_and_decrypt_chinese(): void
    {
        $cipher = new CipherService(self::HASH_KEY, self::HASH_IV);
        $original = '{"商品名稱":"測試商品","數量":10}';

        $encrypted = $cipher->encrypt($original);
        $decrypted = $cipher->decrypt($encrypted);

        $this->assertEquals($original, $decrypted);
    }

    /** @test */
    public function cipher_service_throws_on_empty_hash_key(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('HashKey');

        new CipherService('', self::HASH_IV);
    }

    /** @test */
    public function cipher_service_throws_on_empty_hash_iv(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessage('HashIV');

        new CipherService(self::HASH_KEY, '');
    }

    // ========================================
    // PayloadEncoder 測試
    // ========================================

    /** @test */
    public function payload_encoder_implements_interface(): void
    {
        $encoder = PayloadEncoder::create(self::HASH_KEY, self::HASH_IV);

        $this->assertInstanceOf(PayloadEncoderInterface::class, $encoder);
    }

    /** @test */
    public function payload_encoder_can_encode_and_decode(): void
    {
        $encoder = PayloadEncoder::create(self::HASH_KEY, self::HASH_IV);
        $payload = [
            'MerchantID' => self::MERCHANT_ID,
            'Data' => [
                'MerchantID' => self::MERCHANT_ID,
                'RelateNumber' => 'TEST123',
                'Amount' => 1000,
            ],
        ];

        $encoded = $encoder->encodePayload($payload);

        $this->assertArrayHasKey('Data', $encoded);
        $this->assertIsString($encoded['Data']);
        $this->assertEquals(self::MERCHANT_ID, $encoded['MerchantID']);

        $decoded = $encoder->decodeData($encoded['Data']);

        $this->assertEquals('TEST123', $decoded['RelateNumber']);
        $this->assertEquals(1000, $decoded['Amount']);
    }

    /** @test */
    public function payload_encoder_verify_response_returns_true_on_valid_data(): void
    {
        $encoder = PayloadEncoder::create(self::HASH_KEY, self::HASH_IV);
        $payload = ['Data' => ['MerchantID' => self::MERCHANT_ID]];
        $encoded = $encoder->encodePayload($payload);

        $result = $encoder->verifyResponse(['Data' => $encoded['Data']]);

        $this->assertTrue($result);
    }

    /** @test */
    public function payload_encoder_verify_response_returns_false_on_invalid_data(): void
    {
        $encoder = PayloadEncoder::create(self::HASH_KEY, self::HASH_IV);

        $this->assertFalse($encoder->verifyResponse(['Data' => 'invalid_encrypted_data']));
        $this->assertFalse($encoder->verifyResponse(['Data' => null]));
        $this->assertFalse($encoder->verifyResponse([]));
    }

    // ========================================
    // ItemCollection 測試
    // ========================================

    /** @test */
    public function item_collection_works_with_invoice_item_dto(): void
    {
        $collection = new ItemCollection();

        $item1 = new InvoiceItemDto('商品A', 2, '個', 100.0);
        $item2 = new InvoiceItemDto('商品B', 1, '件', 500.0);

        $collection->add($item1)->add($item2);

        $this->assertEquals(2, $collection->count());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->isNotEmpty());
    }

    /** @test */
    public function item_collection_works_with_allowance_item_dto(): void
    {
        $collection = new ItemCollection();

        $item = new AllowanceItemDto('折讓商品', 1, '個', 100.0);
        $collection->add($item);

        $this->assertEquals(1, $collection->count());
    }

    /** @test */
    public function item_collection_to_array_returns_correct_structure(): void
    {
        $collection = new ItemCollection();
        $collection->add(new InvoiceItemDto('商品', 1, '個', 100.0));

        $array = $collection->toArray();

        $this->assertCount(1, $array);
        $this->assertArrayHasKey('ItemName', $array[0]);
        $this->assertArrayHasKey('ItemCount', $array[0]);
        $this->assertArrayHasKey('ItemPrice', $array[0]);
        $this->assertArrayHasKey('ItemAmount', $array[0]);
    }

    /** @test */
    public function item_collection_can_filter_items(): void
    {
        $collection = new ItemCollection();
        $collection->add(new InvoiceItemDto('便宜商品', 1, '個', 50.0));
        $collection->add(new InvoiceItemDto('貴商品', 1, '個', 500.0));

        $filtered = $collection->filter(function ($item) {
            return $item->toArray()['ItemAmount'] >= 100;
        });

        $this->assertEquals(1, $filtered->count());
    }

    /** @test */
    public function item_collection_can_map_items(): void
    {
        $collection = new ItemCollection();
        $collection->add(new InvoiceItemDto('商品A', 1, '個', 100.0));
        $collection->add(new InvoiceItemDto('商品B', 1, '個', 200.0));

        $amounts = $collection->map(fn($item) => $item->toArray()['ItemAmount']);

        $this->assertEquals([100.0, 200.0], $amounts);
        $this->assertEquals(300.0, array_sum($amounts));
    }

    // ========================================
    // RqHeaderDto 測試
    // ========================================

    /** @test */
    public function rq_header_dto_generates_valid_timestamp(): void
    {
        $before = time();
        $header = new RqHeaderDto();
        $after = time();

        $timestamp = $header->getTimestamp();

        $this->assertGreaterThanOrEqual($before, $timestamp);
        $this->assertLessThanOrEqual($after, $timestamp);
    }

    /** @test */
    public function rq_header_dto_to_payload_returns_correct_structure(): void
    {
        $header = new RqHeaderDto(1700000000);

        $payload = $header->toPayload();

        $this->assertArrayHasKey('Timestamp', $payload);
        $this->assertEquals(1700000000, $payload['Timestamp']);
    }

    // ========================================
    // AbstractContent 繼承測試
    // ========================================

    /** @test */
    public function content_inherits_abstract_content_correctly(): void
    {
        $invoice = new Invoice(self::MERCHANT_ID, self::HASH_KEY, self::HASH_IV);

        $this->assertEquals(self::MERCHANT_ID, $invoice->getMerchantID());
    }

    /** @test */
    public function content_get_payload_encoder_returns_interface(): void
    {
        $invoice = new Invoice(self::MERCHANT_ID, self::HASH_KEY, self::HASH_IV);

        $encoder = $invoice->getPayloadEncoder();

        $this->assertInstanceOf(PayloadEncoderInterface::class, $encoder);
    }

    /** @test */
    public function content_get_rq_header_returns_valid_dto(): void
    {
        $invoice = new Invoice(self::MERCHANT_ID, self::HASH_KEY, self::HASH_IV);

        $header = $invoice->getRqHeader();

        $this->assertInstanceOf(RqHeaderDto::class, $header);
        $this->assertGreaterThan(0, $header->getTimestamp());
    }

    /** @test */
    public function content_can_set_custom_rq_header(): void
    {
        $invoice = new Invoice(self::MERCHANT_ID, self::HASH_KEY, self::HASH_IV);
        $customHeader = new RqHeaderDto(1700000000);

        $invoice->setRqHeader($customHeader);

        $this->assertEquals(1700000000, $invoice->getRqHeader()->getTimestamp());
    }

    /** @test */
    public function content_can_set_custom_payload_encoder(): void
    {
        $invoice = new Invoice(self::MERCHANT_ID, self::HASH_KEY, self::HASH_IV);
        $customEncoder = PayloadEncoder::create('customKey12345!!', 'customIV1234567!');

        $invoice->setPayloadEncoder($customEncoder);

        $this->assertSame($customEncoder, $invoice->getPayloadEncoder());
    }

    // ========================================
    // ValidationException 測試
    // ========================================

    /** @test */
    public function validation_exception_provides_field_info(): void
    {
        $exception = ValidationException::requiredField('TestField');

        $this->assertStringContainsString('TestField', $exception->getMessage());
        $this->assertEquals('TestField', $exception->getField());
    }

    /** @test */
    public function validation_exception_too_long_provides_context(): void
    {
        $exception = ValidationException::tooLong('RelateNumber', 30);

        $this->assertStringContainsString('30', $exception->getMessage());
        $this->assertEquals(30, $exception->getContext()['max_length']);
    }
}


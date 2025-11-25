<?php

use CarlLee\EcPayB2C\Infrastructure\CipherService;
use CarlLee\EcPayB2C\Infrastructure\PayloadEncoder;
use PHPUnit\Framework\TestCase;

class PayloadEncoderTest extends TestCase
{
    private PayloadEncoder $encoder;

    protected function setUp(): void
    {
        $cipher = new CipherService('ejCk326UnaZWKisg', 'q9jcZX8Ib9LM8wYk');
        $this->encoder = new PayloadEncoder($cipher);
    }

    public function testEncodeAndDecodePayload()
    {
        $payload = [
            'MerchantID' => 'TEST_MERCHANT',
            'RqHeader' => ['Timestamp' => 1234567890],
            'Data' => [
                'Field' => 'Value',
                'Items' => [
                    ['Name' => 'Item A', 'Price' => 100],
                ],
            ],
        ];

        $encoded = $this->encoder->encodePayload($payload);

        $this->assertArrayHasKey('Data', $encoded);
        $this->assertIsString($encoded['Data']);
        $this->assertNotEmpty($encoded['Data']);
        $this->assertNotEquals($payload['Data'], $encoded['Data']);

        $decoded = $this->encoder->decodeData($encoded['Data']);

        $this->assertEquals($payload['Data'], $decoded);
    }

    public function testEncodePayloadWithoutDataThrowsException()
    {
        $this->expectException(Exception::class);

        $payload = [
            'MerchantID' => 'TEST_MERCHANT',
            'RqHeader' => ['Timestamp' => 123],
        ];

        $this->encoder->encodePayload($payload);
    }

    public function testDecodeInvalidDataThrowsException()
    {
        $this->expectException(Exception::class);

        $this->encoder->decodeData('invalid-data');
    }
}

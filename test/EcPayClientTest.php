<?php

use CarlLee\EcPayB2C\Contracts\CommandInterface;
use CarlLee\EcPayB2C\EcPayClient;
use CarlLee\EcPayB2C\Infrastructure\CipherService;
use CarlLee\EcPayB2C\Infrastructure\PayloadEncoder;
use CarlLee\EcPayB2C\Request;
use CarlLee\EcPayB2C\Response as EcPayResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class EcPayClientTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private string $hashKey = 'ejCk326UnaZWKisg';
    private string $hashIV = 'q9jcZX8Ib9LM8wYk';
    private string $server = 'https://testing.local';

    protected function tearDown(): void
    {
        Request::setHttpClient(null);
        Mockery::close();
    }

    private function encryptData(array $data): string
    {
        $encoder = new PayloadEncoder(new CipherService($this->hashKey, $this->hashIV));
        $payload = [
            'Data' => $data,
        ];

        $encoded = $encoder->encodePayload($payload);

        return $encoded['Data'];
    }

    public function testSendSuccess()
    {
        // Mock Response Data
        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => 'Success',
            'InvoiceNo' => 'AB12345678'
        ];
        $encryptedData = $this->encryptData($responseData);

        $mockResponseBody = json_encode([
            'Data' => $encryptedData,
            'TransCode' => 1,
            'TransMsg' => 'Success'
        ]);

        // Setup Guzzle Mock
        $mock = new MockHandler([
            new Response(200, [], $mockResponseBody),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Inject Client
        Request::setHttpClient($client);

        // Test EcPayClient
        $ecPayClient = new EcPayClient($this->server, $this->hashKey, $this->hashIV);

        // Mock Command
        $invoice = Mockery::mock(CommandInterface::class);
        $invoice->shouldReceive('setHashKey')->with($this->hashKey);
        $invoice->shouldReceive('setHashIV')->with($this->hashIV);
        $invoice->shouldReceive('getPayload')->andReturn([
            'MerchantID' => 'TEST_MERCHANT_ID',
            'RqHeader' => ['Timestamp' => time()],
            'Data' => ['Example' => 'value'],
        ]);
        $invoice->shouldReceive('getPayloadEncoder')->andReturn(new PayloadEncoder(new CipherService($this->hashKey, $this->hashIV)));
        $invoice->shouldReceive('getRequestPath')->andReturn('/test/api');

        $response = $ecPayClient->send($invoice);

        $this->assertInstanceOf(EcPayResponse::class, $response);
        $data = $response->getData();
        $this->assertEquals(1, $data['RtnCode']);
        $this->assertEquals('AB12345678', $data['InvoiceNo']);
    }

    public function testSendFailWithNoData()
    {
        // Simulate error where Data is empty but TransCode shows error
        $mockResponseBody = json_encode([
            'Data' => '',
            'TransCode' => 0,
            'TransMsg' => 'Error Occurred'
        ]);

        $mock = new MockHandler([
            new Response(200, [], $mockResponseBody),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $ecPayClient = new EcPayClient($this->server, $this->hashKey, $this->hashIV);

        $invoice = Mockery::mock(CommandInterface::class);
        $invoice->shouldReceive('setHashKey');
        $invoice->shouldReceive('setHashIV');
        $invoice->shouldReceive('getPayload')->andReturn([
            'MerchantID' => 'TEST_MERCHANT_ID',
            'RqHeader' => ['Timestamp' => time()],
            'Data' => [],
        ]);
        $invoice->shouldReceive('getPayloadEncoder')->andReturn(new PayloadEncoder(new CipherService($this->hashKey, $this->hashIV)));
        $invoice->shouldReceive('getRequestPath')->andReturn('/test/api');

        $response = $ecPayClient->send($invoice);
        $data = $response->getData();

        $this->assertEquals(0, $data['RtnCode']);
        $this->assertEquals('Error Occurred', $data['RtnMsg']);
    }

    public function testDecryptionError()
    {
        // Invalid encrypted data
        $mockResponseBody = json_encode([
            'Data' => 'invalid_base64_string',
            'TransCode' => 1,
            'TransMsg' => 'Success'
        ]);

        $mock = new MockHandler([
            new Response(200, [], $mockResponseBody),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $ecPayClient = new EcPayClient($this->server, $this->hashKey, $this->hashIV);

        $invoice = Mockery::mock(CommandInterface::class);
        $invoice->shouldReceive('setHashKey');
        $invoice->shouldReceive('setHashIV');
        $invoice->shouldReceive('getPayload')->andReturn([
            'MerchantID' => 'TEST_MERCHANT_ID',
            'RqHeader' => ['Timestamp' => time()],
            'Data' => [],
        ]);
        $invoice->shouldReceive('getPayloadEncoder')->andReturn(new PayloadEncoder(new CipherService($this->hashKey, $this->hashIV)));
        $invoice->shouldReceive('getRequestPath')->andReturn('/test/api');

        $this->expectException(Exception::class);
        // Note: EcPayClient throws generic Exception for json error after decryption fail (decryption returns false/garbage)
        // "The response data format is invalid." is thrown if json_decode fails.

        $ecPayClient->send($invoice);
    }
}

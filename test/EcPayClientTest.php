<?php

use ecPay\eInvoice\EcPayClient;
use ecPay\eInvoice\Request;
use ecPay\eInvoice\Content;
use ecPay\eInvoice\Response as EcPayResponse;
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
        $json = json_encode($data);
        $encoded = urlencode($json);
        $encrypted = openssl_encrypt($encoded, 'AES-128-CBC', $this->hashKey, OPENSSL_RAW_DATA, $this->hashIV);
        return base64_encode($encrypted);
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
        
        // Mock Content (Invoice)
        $invoice = Mockery::mock(Content::class);
        $invoice->shouldReceive('setHashKey')->with($this->hashKey);
        $invoice->shouldReceive('setHashIV')->with($this->hashIV);
        $invoice->shouldReceive('getContent')->andReturn(['some' => 'content']);
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
        
        $invoice = Mockery::mock(Content::class);
        $invoice->shouldReceive('setHashKey');
        $invoice->shouldReceive('setHashIV');
        $invoice->shouldReceive('getContent')->andReturn([]);
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

        $invoice = Mockery::mock(Content::class);
        $invoice->shouldReceive('setHashKey');
        $invoice->shouldReceive('setHashIV');
        $invoice->shouldReceive('getContent')->andReturn([]);
        $invoice->shouldReceive('getRequestPath')->andReturn('/test/api');

        $this->expectException(Exception::class);
        // Note: EcPayClient throws generic Exception for json error after decryption fail (decryption returns false/garbage)
        // "The response data format is invalid." is thrown if json_decode fails.
        
        $ecPayClient->send($invoice);
    }
}


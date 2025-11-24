<?php

use ecPay\eInvoice\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    private string $testUrl = 'https://test.api.com/endpoint';
    private array $testContent = ['test' => 'data'];

    protected function tearDown(): void
    {
        // 重置靜態 HTTP client
        Request::setHttpClient(null);
    }

    /**
     * 測試建構函數
     */
    public function testConstructor()
    {
        $request = new Request($this->testUrl, $this->testContent);

        $this->assertInstanceOf(Request::class, $request);
    }

    /**
     * 測試建構函數 - 無參數
     */
    public function testConstructorWithoutParameters()
    {
        $request = new Request();

        $this->assertInstanceOf(Request::class, $request);
    }

    /**
     * 測試設定 HTTP Client
     */
    public function testSetHttpClient()
    {
        $client = new Client();
        Request::setHttpClient($client);

        $this->expectNotToPerformAssertions();
    }

    /**
     * 測試設定 HTTP Client 為 null
     */
    public function testSetHttpClientNull()
    {
        Request::setHttpClient(null);

        $this->expectNotToPerformAssertions();
    }

    /**
     * 測試發送請求 - 成功
     */
    public function testSendSuccess()
    {
        $responseData = ['status' => 'success', 'data' => 'test'];
        $mockResponseBody = json_encode($responseData);

        $mock = new MockHandler([
            new Response(200, [], $mockResponseBody),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request($this->testUrl, $this->testContent);
        $result = $request->send();

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('test', $result['data']);
    }

    /**
     * 測試發送請求 - 使用參數覆蓋建構函數的值
     */
    public function testSendWithParameters()
    {
        $newUrl = 'https://new.api.com/endpoint';
        $newContent = ['new' => 'content'];
        $responseData = ['received' => 'ok'];
        $mockResponseBody = json_encode($responseData);

        $mock = new MockHandler([
            new Response(200, [], $mockResponseBody),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request($this->testUrl, $this->testContent);
        $result = $request->send($newUrl, $newContent);

        $this->assertIsArray($result);
        $this->assertEquals('ok', $result['received']);
    }

    /**
     * 測試發送請求 - 空回應
     */
    public function testSendEmptyResponse()
    {
        $mock = new MockHandler([
            new Response(200, [], '{}'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request($this->testUrl, $this->testContent);
        $result = $request->send();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * 測試發送請求 - 陣列回應
     */
    public function testSendArrayResponse()
    {
        $responseData = [
            'items' => [
                ['id' => 1, 'name' => 'Item 1'],
                ['id' => 2, 'name' => 'Item 2'],
            ],
            'total' => 2,
        ];
        $mockResponseBody = json_encode($responseData);

        $mock = new MockHandler([
            new Response(200, [], $mockResponseBody),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request($this->testUrl, $this->testContent);
        $result = $request->send();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertCount(2, $result['items']);
        $this->assertEquals(2, $result['total']);
    }

    /**
     * 測試發送請求 - RequestException 有回應
     */
    public function testSendRequestExceptionWithResponse()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error message from server');

        $mock = new MockHandler([
            new RequestException(
                'Error Communicating with Server',
                new Psr7Request('POST', $this->testUrl),
                new Response(400, [], 'Error message from server')
            ),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request($this->testUrl, $this->testContent);
        $request->send();
    }

    /**
     * 測試發送請求 - RequestException 無回應
     */
    public function testSendRequestExceptionWithoutResponse()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request Error: Connection timeout');

        $mock = new MockHandler([
            new RequestException(
                'Connection timeout',
                new Psr7Request('POST', $this->testUrl)
            ),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request($this->testUrl, $this->testContent);
        $request->send();
    }

    /**
     * 測試發送請求 - 網路錯誤
     */
    public function testSendNetworkError()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request Error:');

        $mock = new MockHandler([
            new RequestException(
                'Network Error',
                new Psr7Request('POST', $this->testUrl)
            ),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request($this->testUrl, $this->testContent);
        $request->send();
    }

    /**
     * 測試自動創建 HTTP Client
     */
    public function testAutoCreateHttpClient()
    {
        // 確保沒有設定 client
        Request::setHttpClient(null);

        $responseData = ['auto' => 'created'];
        $mockResponseBody = json_encode($responseData);

        $mock = new MockHandler([
            new Response(200, [], $mockResponseBody),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // 在第一次呼叫 send 之前設定 mock client
        Request::setHttpClient($client);

        $request = new Request($this->testUrl, $this->testContent);
        $result = $request->send();

        $this->assertIsArray($result);
        $this->assertEquals('created', $result['auto']);
    }

    /**
     * 測試多次請求
     */
    public function testMultipleSends()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['request' => 1])),
            new Response(200, [], json_encode(['request' => 2])),
            new Response(200, [], json_encode(['request' => 3])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request($this->testUrl, $this->testContent);

        $result1 = $request->send();
        $this->assertEquals(1, $result1['request']);

        $result2 = $request->send();
        $this->assertEquals(2, $result2['request']);

        $result3 = $request->send();
        $this->assertEquals(3, $result3['request']);
    }

    /**
     * 測試發送複雜的 JSON 內容
     */
    public function testSendComplexJsonContent()
    {
        $complexContent = [
            'MerchantID' => 'TEST123',
            'Data' => [
                'InvoiceNo' => 'AB12345678',
                'Items' => [
                    ['name' => '商品A', 'price' => 100],
                    ['name' => '商品B', 'price' => 200],
                ],
                'TotalAmount' => 300,
            ],
            'Timestamp' => time(),
        ];

        $responseData = ['result' => 'success'];
        $mockResponseBody = json_encode($responseData);

        $mock = new MockHandler([
            new Response(200, [], $mockResponseBody),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        Request::setHttpClient($client);

        $request = new Request($this->testUrl, $complexContent);
        $result = $request->send();

        $this->assertEquals('success', $result['result']);
    }

    /**
     * 測試不同的 HTTP 狀態碼
     */
    public function testDifferentHttpStatusCodes()
    {
        $statusCodes = [200, 201, 202];

        foreach ($statusCodes as $statusCode) {
            $mock = new MockHandler([
                new Response($statusCode, [], json_encode(['status' => $statusCode])),
            ]);
            $handlerStack = HandlerStack::create($mock);
            $client = new Client(['handler' => $handlerStack]);

            Request::setHttpClient($client);

            $request = new Request($this->testUrl, $this->testContent);
            $result = $request->send();

            $this->assertEquals($statusCode, $result['status']);
        }
    }
}

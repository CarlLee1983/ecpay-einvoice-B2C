<?php

use CarlLee\EcPayB2C\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /**
     * 測試建構函數 - 無參數
     */
    public function testConstructorWithoutData()
    {
        $response = new Response();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->success());
        $this->assertEquals('', $response->getMessage());

        $data = $response->getData();
        $this->assertEquals(0, $data['RtnCode']);
        $this->assertEquals('', $data['RtnMsg']);
    }

    /**
     * 測試建構函數 - 帶參數
     */
    public function testConstructorWithData()
    {
        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => 'Success',
            'InvoiceNo' => 'AB12345678',
        ];

        $response = new Response($responseData);

        $this->assertTrue($response->success());
        $this->assertEquals('Success', $response->getMessage());

        $data = $response->getData();
        $this->assertEquals(1, $data['RtnCode']);
        $this->assertEquals('Success', $data['RtnMsg']);
        $this->assertEquals('AB12345678', $data['InvoiceNo']);
    }

    /**
     * 測試 setData 方法
     */
    public function testSetData()
    {
        $response = new Response();

        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => 'Invoice created',
            'InvoiceNo' => 'CD98765432',
        ];

        $result = $response->setData($responseData);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue($response->success());
        $this->assertEquals('Invoice created', $response->getMessage());

        $data = $response->getData();
        $this->assertEquals('CD98765432', $data['InvoiceNo']);
    }

    /**
     * 測試 success 方法 - 成功回應
     */
    public function testSuccessWithRtnCode1()
    {
        $response = new Response([
            'RtnCode' => 1,
            'RtnMsg' => 'Success',
        ]);

        $this->assertTrue($response->success());
    }

    /**
     * 測試 success 方法 - 失敗回應（RtnCode = 0）
     */
    public function testSuccessWithRtnCode0()
    {
        $response = new Response([
            'RtnCode' => 0,
            'RtnMsg' => 'Error occurred',
        ]);

        $this->assertFalse($response->success());
    }

    /**
     * 測試 success 方法 - 失敗回應（其他錯誤碼）
     */
    public function testSuccessWithOtherErrorCode()
    {
        $response = new Response([
            'RtnCode' => -1,
            'RtnMsg' => 'System error',
        ]);

        $this->assertFalse($response->success());
    }

    /**
     * 測試 getMessage 方法 - 成功訊息
     */
    public function testGetMessageSuccess()
    {
        $response = new Response([
            'RtnCode' => 1,
            'RtnMsg' => '發票開立成功',
        ]);

        $this->assertEquals('發票開立成功', $response->getMessage());
    }

    /**
     * 測試 getMessage 方法 - 錯誤訊息
     */
    public function testGetMessageError()
    {
        $response = new Response([
            'RtnCode' => 0,
            'RtnMsg' => '發票號碼格式錯誤',
        ]);

        $this->assertEquals('發票號碼格式錯誤', $response->getMessage());
    }

    /**
     * 測試 getData 方法 - 完整資料
     */
    public function testGetDataWithFullResponse()
    {
        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => 'Success',
            'InvoiceNo' => 'AB12345678',
            'InvoiceDate' => '2024-01-01',
            'RandomNumber' => '1234',
        ];

        $response = new Response($responseData);
        $data = $response->getData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('RtnCode', $data);
        $this->assertArrayHasKey('RtnMsg', $data);
        $this->assertArrayHasKey('InvoiceNo', $data);
        $this->assertArrayHasKey('InvoiceDate', $data);
        $this->assertArrayHasKey('RandomNumber', $data);

        $this->assertEquals('AB12345678', $data['InvoiceNo']);
        $this->assertEquals('2024-01-01', $data['InvoiceDate']);
        $this->assertEquals('1234', $data['RandomNumber']);
    }

    /**
     * 測試 getData 方法 - 空陣列時使用預設值
     */
    public function testGetDataWithEmptyArray()
    {
        $response = new Response([]);
        $data = $response->getData();

        $this->assertIsArray($data);
        // 傳入空陣列時，因為 !empty([]) 為 true，不會呼叫 setData
        // 所以會保留預設的 RtnCode 和 RtnMsg
        $this->assertArrayHasKey('RtnCode', $data);
        $this->assertArrayHasKey('RtnMsg', $data);
        $this->assertEquals(0, $data['RtnCode']);
        $this->assertEquals('', $data['RtnMsg']);
    }

    /**
     * 測試 setData 方法的 Fluent Interface
     */
    public function testSetDataFluentInterface()
    {
        $response = new Response();

        $result = $response
            ->setData(['RtnCode' => 1, 'RtnMsg' => 'First'])
            ->setData(['RtnCode' => 1, 'RtnMsg' => 'Second']);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals('Second', $response->getMessage());
    }

    /**
     * 測試多種錯誤情境
     */
    public function testVariousErrorScenarios()
    {
        $errorScenarios = [
            ['RtnCode' => 0, 'RtnMsg' => 'Invoice number already exists'],
            ['RtnCode' => 0, 'RtnMsg' => 'Invalid tax ID'],
            ['RtnCode' => 0, 'RtnMsg' => 'Missing required fields'],
            ['RtnCode' => -999, 'RtnMsg' => 'System maintenance'],
        ];

        foreach ($errorScenarios as $scenario) {
            $response = new Response($scenario);
            $this->assertFalse($response->success(), 'Failed for: ' . $scenario['RtnMsg']);
        }
    }

    /**
     * 測試成功回應包含額外資料
     */
    public function testSuccessResponseWithAdditionalData()
    {
        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => 'Success',
            'InvoiceNo' => 'AB12345678',
            'InvoiceDate' => '2024-01-15',
            'RandomNumber' => '5678',
            'BarCode' => '12345678901234567890',
            'QRCodeLeft' => 'QR_LEFT_DATA',
            'QRCodeRight' => 'QR_RIGHT_DATA',
        ];

        $response = new Response($responseData);

        $this->assertTrue($response->success());

        $data = $response->getData();
        $this->assertEquals('AB12345678', $data['InvoiceNo']);
        $this->assertEquals('2024-01-15', $data['InvoiceDate']);
        $this->assertEquals('5678', $data['RandomNumber']);
        $this->assertEquals('12345678901234567890', $data['BarCode']);
        $this->assertEquals('QR_LEFT_DATA', $data['QRCodeLeft']);
        $this->assertEquals('QR_RIGHT_DATA', $data['QRCodeRight']);
    }

    /**
     * 測試查詢發票回應
     */
    public function testQueryInvoiceResponse()
    {
        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => 'Success',
            'InvoiceNo' => 'AB12345678',
            'InvoiceDate' => '2024-01-15 10:30:00',
            'InvoiceStatus' => '1',
            'TotalAmount' => 1000,
        ];

        $response = new Response($responseData);

        $this->assertTrue($response->success());
        $data = $response->getData();
        $this->assertEquals('AB12345678', $data['InvoiceNo']);
        $this->assertEquals('1', $data['InvoiceStatus']);
        $this->assertEquals(1000, $data['TotalAmount']);
    }

    /**
     * 測試作廢發票回應
     */
    public function testInvalidInvoiceResponse()
    {
        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => '作廢成功',
            'InvoiceNo' => 'AB12345678',
        ];

        $response = new Response($responseData);

        $this->assertTrue($response->success());
        $this->assertEquals('作廢成功', $response->getMessage());
    }

    /**
     * 測試折讓發票回應
     */
    public function testAllowanceResponse()
    {
        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => '折讓成功',
            'InvoiceNo' => 'AB12345678',
            'AllowanceNo' => 'AL12345678',
        ];

        $response = new Response($responseData);

        $this->assertTrue($response->success());
        $data = $response->getData();
        $this->assertEquals('AB12345678', $data['InvoiceNo']);
        $this->assertEquals('AL12345678', $data['AllowanceNo']);
    }

    /**
     * 測試檢查愛心碼回應
     */
    public function testCheckLoveCodeResponse()
    {
        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => '愛心碼有效',
            'IsExist' => 'Y',
        ];

        $response = new Response($responseData);

        $this->assertTrue($response->success());
        $data = $response->getData();
        $this->assertEquals('Y', $data['IsExist']);
    }

    /**
     * 測試檢查載具條碼回應
     */
    public function testCheckBarcodeResponse()
    {
        $responseData = [
            'RtnCode' => 1,
            'RtnMsg' => '載具條碼有效',
            'IsExist' => 'Y',
        ];

        $response = new Response($responseData);

        $this->assertTrue($response->success());
        $data = $response->getData();
        $this->assertEquals('Y', $data['IsExist']);
    }

    /**
     * 測試 RtnCode 為字串的情況
     */
    public function testSuccessWithStringRtnCode()
    {
        $response = new Response([
            'RtnCode' => '1',
            'RtnMsg' => 'Success',
        ]);

        // PHP 會進行鬆散比較，'1' == 1 為 true
        $this->assertTrue($response->success());
    }

    /**
     * 測試缺少 RtnMsg 的情況
     */
    public function testResponseWithoutRtnMsg()
    {
        $response = new Response([
            'RtnCode' => 1,
        ]);

        $this->assertTrue($response->success());
        // getMessage 應該返回空字串或 null（根據實作）
    }
}

<?php

class CheckLoveCodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->client = new CarlLee\EcPayB2C\EcPayClient(
            $_ENV['SERVER'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
        $this->instance = new CarlLee\EcPayB2C\Queries\CheckLoveCode(
            $_ENV['MERCHANT_ID'],
            $_ENV['HASH_KEY'],
            $_ENV['HASH_IV']
        );
    }

    public function testQuickCheck()
    {
        $this->instance->setLoveCode($_ENV['LOVECODE']);
        $response = $this->client->send($this->instance);

        $this->assertTrue($response->success());
    }
}

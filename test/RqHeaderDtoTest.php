<?php

use CarlLee\EcPayB2C\DTO\RqHeaderDto;
use PHPUnit\Framework\TestCase;

class RqHeaderDtoTest extends TestCase
{
    public function testDefaultTimestampIsFilled()
    {
        $dto = new RqHeaderDto();

        $this->assertGreaterThan(0, $dto->getTimestamp());
        $this->assertArrayHasKey('Timestamp', $dto->toPayload());
    }

    public function testSetTimestamp()
    {
        $dto = new RqHeaderDto(1234567890);

        $this->assertSame(1234567890, $dto->getTimestamp());

        $dto->setTimestamp(987654321);
        $this->assertSame(987654321, $dto->getTimestamp());
    }

    public function testInvalidTimestampThrows()
    {
        $this->expectException(\InvalidArgumentException::class);

        new RqHeaderDto(0);
    }
}

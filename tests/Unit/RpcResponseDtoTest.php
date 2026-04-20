<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Unomns\Centrifugo\Dto\RpcResponseDto;
use Unomns\Centrifugo\Response\ErrorResponse;
use Unomns\Centrifugo\Response\RpcProxyResponse;

class RpcResponseDtoTest extends TestCase
{
    public function test_has_error_false_when_no_error(): void
    {
        $dto = new RpcResponseDto(data: ['key' => 'value']);

        $this->assertFalse($dto->hasError());
    }

    public function test_has_error_true_when_error_set(): void
    {
        $dto = new RpcResponseDto(error: new ErrorResponse(500, 'Internal error'));

        $this->assertTrue($dto->hasError());
    }

    public function test_proxy_response_serializes_error(): void
    {
        $dto      = new RpcResponseDto(error: new ErrorResponse(403, 'Forbidden'));
        $response = new RpcProxyResponse($dto);

        $this->assertSame(
            ['error' => ['code' => 403, 'message' => 'Forbidden']],
            $response->jsonSerialize(),
        );
    }

    public function test_proxy_response_serializes_data(): void
    {
        $dto      = new RpcResponseDto(data: ['foo' => 'bar']);
        $response = new RpcProxyResponse($dto);

        $this->assertSame(
            ['result' => ['data' => ['foo' => 'bar']]],
            $response->jsonSerialize(),
        );
    }

    public function test_proxy_response_serializes_empty_data_as_object(): void
    {
        $dto      = new RpcResponseDto();
        $response = new RpcProxyResponse($dto);
        $json     = $response->jsonSerialize();

        $this->assertArrayHasKey('result', $json);
        $this->assertInstanceOf(\stdClass::class, $json['result']);
    }
}

<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Contracts;

use Unomns\Centrifugo\Dto\RpcRequestDto;
use Unomns\Centrifugo\Dto\RpcResponseDto;
use Unomns\Centrifugo\Dto\SubscribeRequestDto;
use Unomns\Centrifugo\Response\DisconnectResponse;
use Unomns\Centrifugo\Response\ErrorResponse;
use Unomns\Centrifugo\Response\SubscribeResult;

interface ChannelHandlerInterface
{
    public function subscribe(SubscribeRequestDto $dto): SubscribeResult|ErrorResponse|DisconnectResponse;

    public function rpc(RpcRequestDto $dto): RpcResponseDto;
}

<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Unomns\Centrifugo\Dto\SubscribeRequestDto;

class SubscribeProxyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client'    => ['required', 'string', 'min:1'],
            'transport' => ['sometimes', 'string', 'in:websocket,sockjs'],
            'protocol'  => ['sometimes', 'string', 'in:json,protobuf'],
            'encoding'  => ['sometimes', 'string', 'in:json,binary'],
            'user'      => ['nullable', 'string'],
            'channel'   => ['required', 'string', 'min:1'],
            'meta'      => ['nullable', 'array'],
            'data'      => ['nullable', 'array'],
            'b64data'   => ['nullable', 'string'],
        ];
    }

    public function dto(): SubscribeRequestDto
    {
        $channel   = $this->string('channel')->toString();
        $namespace = $channel;
        $method    = null;

        if (str_contains($channel, ':')) {
            [$namespace, $method] = explode(':', $channel, 2);
        }

        return new SubscribeRequestDto(
            userId:    $this->input('user', ''),
            namespace: $namespace,
            method:    $method,
            client:    $this->string('client')->toString(),
            meta:      $this->array('meta'),
            data:      $this->array('data'),
        );
    }
}

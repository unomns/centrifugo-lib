<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Unomns\Centrifugo\Dto\PublishRequestDto;

class PublishProxyRequest extends FormRequest
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
            'transport' => ['sometimes', 'string'],
            'protocol'  => ['sometimes', 'string', 'in:json,protobuf'],
            'encoding'  => ['sometimes', 'string', 'in:json,binary'],
            'user'      => ['nullable', 'string'],
            'channel'   => ['required', 'string', 'min:1'],
            'data'      => ['nullable', 'array'],
            'b64data'   => ['nullable', 'string'],
            'meta'      => ['nullable', 'array'],
        ];
    }

    public function dto(): PublishRequestDto
    {
        return new PublishRequestDto(
            client:    $this->string('client')->toString(),
            transport: $this->input('transport', ''),
            protocol:  $this->input('protocol', ''),
            encoding:  $this->input('encoding', ''),
            user:      $this->input('user', ''),
            channel:   $this->string('channel')->toString(),
            data:      $this->input('data'),
            b64data:   $this->input('b64data'),
            meta:      $this->input('meta'),
        );
    }
}

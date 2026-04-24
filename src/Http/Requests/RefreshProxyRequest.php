<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Unomns\Centrifugo\Dto\RefreshRequestDto;

class RefreshProxyRequest extends FormRequest
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
            'user'      => ['nullable', 'string'],
            'transport' => ['nullable', 'string'],
            'protocol'  => ['nullable', 'string', 'in:json,protobuf'],
            'encoding'  => ['nullable', 'string', 'in:json,binary'],
            'meta'      => ['nullable', 'array'],
        ];
    }

    public function dto(): RefreshRequestDto
    {
        return new RefreshRequestDto(
            client:    $this->input('client'),
            userId:    $this->input('user') ?: null,
            transport: $this->input('transport', ''),
            protocol:  $this->input('protocol', ''),
            encoding:  $this->input('encoding', ''),
            meta:      $this->input('meta'),
        );
    }
}

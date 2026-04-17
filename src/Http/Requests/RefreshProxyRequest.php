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

    public function rules(): array
    {
        return [
            'client'    => ['required', 'string', 'min:1'],
            'user'      => ['nullable', 'string'],
            'transport' => ['nullable', 'string'],
            'protocol'  => ['nullable', 'string', 'in:json,protobuf'],
            'encoding'  => ['nullable', 'string', 'in:json,binary'],
        ];
    }

    public function dto(): RefreshRequestDto
    {
        return new RefreshRequestDto(
            client: $this->input('client'),
            userId: $this->input('user') ?: null,
        );
    }
}

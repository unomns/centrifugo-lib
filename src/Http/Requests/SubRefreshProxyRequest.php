<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Unomns\Centrifugo\Dto\SubRefreshRequestDto;

class SubRefreshProxyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client'    => ['required', 'string', 'min:1'],
            'channel'   => ['required', 'string', 'min:1'],
            'user'      => ['nullable', 'string'],
            'transport' => ['nullable', 'string'],
            'protocol'  => ['nullable', 'string', 'in:json,protobuf'],
            'encoding'  => ['nullable', 'string', 'in:json,binary'],
        ];
    }

    public function dto(): SubRefreshRequestDto
    {
        return new SubRefreshRequestDto(
            client:  $this->input('client'),
            channel: $this->input('channel'),
            userId:  $this->input('user') ?: null,
        );
    }
}

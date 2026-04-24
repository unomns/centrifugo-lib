<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Unomns\Centrifugo\Dto\RpcRequestDto;

class RpcProxyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'method'  => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-z_]+:[a-z_]+$/'],
            'client'  => ['required', 'string', 'min:1'],
            'user'    => ['nullable', 'string'],
            'data'    => ['nullable', 'array'],
            'b64data' => ['nullable', 'string'],
            'meta'    => ['nullable', 'array'],
        ];
    }

    public function dto(): RpcRequestDto
    {
        [$namespace, $method] = explode(':', $this->string('method')->toString(), 2);

        return new RpcRequestDto(
            userId:       $this->input('user', ''),
            rpcNamespace: $namespace,
            rpcMethod:    $method,
            data:         $this->array('data'),
            client:       $this->string('client')->toString(),
            b64data:      $this->input('b64data'),
            meta:         $this->input('meta'),
        );
    }
}

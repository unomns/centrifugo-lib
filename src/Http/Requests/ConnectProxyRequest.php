<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Unomns\Centrifugo\Dto\ConnectRequestDto;

/**
 * Centrifugo connect proxy request.
 *
 * Centrifugo sends this when a client connects. The 'client' field is the
 * connection ID assigned by Centrifugo — pass it to TokenFactory::forUser()
 * so the token can be bound to this specific connection:
 *
 *   $token = app(TokenFactory::class)->forUser(
 *       userId: auth()->id(),
 *       client: $request->clientId(),
 *   );
 */
class ConnectProxyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client'    => ['required', 'string'],
            'transport' => ['sometimes', 'string'],
            'protocol'  => ['sometimes', 'string'],
            'encoding'  => ['sometimes', 'string'],
            'data'      => ['nullable', 'array'],
            'b64data'   => ['nullable', 'string'],
            'name'      => ['nullable', 'string'],
            'version'   => ['nullable', 'string'],
            'channels'  => ['nullable', 'array'],
            'channels.*' => ['string'],
        ];
    }

    /** The Centrifugo-assigned connection ID for this client. */
    public function clientId(): string
    {
        return $this->string('client')->toString();
    }

    public function dto(): ConnectRequestDto
    {
        return new ConnectRequestDto(
            client:    $this->string('client')->toString(),
            transport: $this->input('transport', ''),
            protocol:  $this->input('protocol', ''),
            encoding:  $this->input('encoding', ''),
            name:      $this->input('name'),
            version:   $this->input('version'),
            data:      $this->input('data'),
            b64data:   $this->input('b64data'),
            channels:  $this->input('channels'),
        );
    }
}

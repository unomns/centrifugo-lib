<?php

declare(strict_types=1);

namespace Unomns\Centrifugo;

class TokenFactory
{
    public function __construct(
        private readonly CentrifugoManager $manager,
        private readonly int $authTtl,
        private readonly int $anonTtl,
    ) {}

    /**
     * Generate a Centrifugo connection JWT.
     *
     * Anonymous users: pass null, 0, '', or '0' — all coerce to sub=''.
     * Centrifugo requires sub='' (empty string) for anonymous connections;
     * passing null directly to phpcent produces sub='null', breaking auth.
     *
     * Client binding: pass the client ID from ConnectProxyRequest::clientId()
     * to embed it in the token's info claim. This allows Centrifugo to
     * associate the token with the specific connection that requested it.
     *
     * @param string|int|null $userId  Authenticated user ID; null/0/'' for anonymous.
     * @param string|null     $client  Centrifugo client ID from the connect proxy request.
     */
    public function forUser(string|int|null $userId, ?string $client = null): string
    {
        $normalizedId = $this->normalizeUserId($userId);
        $isAuthenticated = $normalizedId !== '';

        $exp  = time() + ($isAuthenticated ? $this->authTtl : $this->anonTtl);
        $info = [];

        if (!$isAuthenticated) {
            $info['nonce'] = $this->resolveAnonNonce();
        }

        if ($client !== null && $client !== '') {
            $info['client'] = $client;
        }

        return $this->manager->generateConnectionToken($normalizedId, $exp, $info);
    }

    /**
     * Generate a Centrifugo subscription JWT for per-channel token auth.
     * Use this when Centrifugo is configured for token-based channel auth
     * instead of proxy-based subscribe auth.
     */
    public function forSubscription(
        string|int|null $userId,
        string $channel,
        ?int $expOverride = null,
    ): string {
        $normalizedId    = $this->normalizeUserId($userId);
        $isAuthenticated = $normalizedId !== '';
        $exp             = time() + ($expOverride ?? ($isAuthenticated ? $this->authTtl : $this->anonTtl));

        return $this->manager->generateSubscriptionToken($normalizedId, $channel, $exp);
    }

    private function normalizeUserId(string|int|null $userId): string
    {
        if ($userId === null || $userId === 0 || $userId === '0' || $userId === '') {
            return '';
        }

        return (string) $userId;
    }

    private function resolveAnonNonce(): string
    {
        $sessionId = function_exists('session') && session()->isStarted()
            ? session()->getId()
            : null;

        return $sessionId ?: md5(request()->ip() . request()->header('User-Agent', ''));
    }
}

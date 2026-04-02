<?php

declare(strict_types=1);

namespace Unomns\Centrifugo;

use Unomns\Centrifugo\Exceptions\HandlerNotFoundException;

class HandlerRegistry
{
    /** @var array<string, string> namespace → FQCN */
    private array $map;

    public function __construct(
        array $initialMap,
        private readonly CentrifugoManager $manager,
    ) {
        $this->map = $initialMap;
    }

    public function register(string $namespace, string $handlerClass): void
    {
        $this->map[$namespace] = $handlerClass;
    }

    public function has(string $namespace): bool
    {
        return isset($this->map[$namespace]);
    }

    /**
     * Resolve a handler instance for the given namespace.
     *
     * Uses the Laravel container so handlers can declare additional
     * constructor dependencies beyond CentrifugoManager.
     *
     * @throws HandlerNotFoundException
     */
    public function resolve(string $namespace): AbstractChannelHandler
    {
        if (!$this->has($namespace)) {
            throw new HandlerNotFoundException(
                "No Centrifugo handler registered for namespace '{$namespace}'."
            );
        }

        $class = $this->map[$namespace];

        return app($class, ['manager' => $this->manager]);
    }

    /** @return array<string, string> */
    public function all(): array
    {
        return $this->map;
    }
}

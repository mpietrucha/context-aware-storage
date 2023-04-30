<?php

namespace Mpietrucha\Storage\Factory;

use Closure;
use Mpietrucha\Storage\Adapter;
use Mpietrucha\Storage\Concerns\HasTable;
use Mpietrucha\Storage\Contracts\ExpiryInterface;
use Mpietrucha\Storage\Resolver\ExpiryDateResolver;

abstract class Expiry implements ExpiryInterface
{
    use HasTable;

    protected ?Closure $onExpiresResolved = null;

    abstract protected function adapter(): Adapter;

    public function expiry(string $key, mixed $expires): void
    {
        if (! $expires) {
            return;
        }

        if ($this->adapter()->exists($key)) {
            return;
        }

        $this->adapter()->put($key, ExpiryDateResolver::create($expiry)->encode($this->onExpiresResolved));
    }

    public function expired(?string $key, Closure $callback): void
    {
        if (! $key) {
            return;
        }

        if (! $expiry = $this->adapter()->get($key)) {
            return;
        }

        if (! ExpiryDateResolver::create($expiry)->expired()) {
            return;
        }

        $this->adapter()->forget($key);

        $callback($key);
    }

    public function onExpiresResolved(Closure $callback): void
    {
        $this->onExpiresResolved = $callback;
    }
}

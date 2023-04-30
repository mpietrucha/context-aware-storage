<?php

namespace Mpietrucha\Storage\Factory;

use Closure;
use Mpietrucha\Storage\Adapter;
use Mpietrucha\Storage\Concerns\HasTable;
use Mpietrucha\Storage\Expiry\CarbonDateResolver;
use Mpietrucha\Storage\Contracts\ExpiryInterface;
use Mpietrucha\Storage\Contracts\ExpiryDateResolverInterface;

abstract class Expiry implements ExpiryInterface
{
    use HasTable;

    protected ?Closure $onExpiresResolved = null;

    abstract protected function adapter(): Adapter;

    public function resolver(mixed $expires): ExpiryDateResolverInterface
    {
        return new CarbonDateResolver($expires);
    }

    public function expiry(string $key, mixed $expires): void
    {
        if (! $expires) {
            return;
        }

        if ($this->adapter()->exists($key)) {
            return;
        }

        $this->adapter()->put($key, $this->resolver($expires)->encode($this->onExpiresResolved));
    }

    public function expired(?string $key, Closure $callback): void
    {
        if (! $key) {
            return;
        }

        if (! $expires = $this->adapter()->get($key)) {
            return;
        }

        if (! $this->resolver($expires)->expired()) {
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

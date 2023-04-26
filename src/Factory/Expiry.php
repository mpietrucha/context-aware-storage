<?php

namespace Mpietrucha\Storage\Factory;

use Closure;
use DateTime;
use Carbon\Carbon;
use Mpietrucha\Support\Types;
use Mpietrucha\Storage\Adapter;
use Mpietrucha\Storage\Concerns\HasTable;
use Mpietrucha\Storage\Contracts\ExpiryInterface;

abstract class Expiry implements ExpiryInterface
{
    use HasTable;

    abstract protected function adapter(): Adapter;

    public function expiry(string $key, mixed $expires): void
    {
        if (! $expires) {
            return;
        }

        if ($this->adapter()->exists($key)) {
            return;
        }

        if ($expires instanceof DateTime) {
            $this->adapter()->put($key, $expires->getTimestamp());

            return;
        }

        if (! Types::array($expires)) {
            $expires = [$expires, 'minutes'];
        }

        $this->adapter()->put($key, Carbon::now()->add(...$expires)->getTimestamp());
    }

    public function expired(?string $key, Closure $callback): void
    {
        if (! $key) {
            return;
        }

        if (! $expiry = $this->adapter()->get($key)) {
            return;
        }

        if (Carbon::createFromTimestamp($expiry)->isAfter(Carbon::now())) {
            return;
        }

        $this->adapter()->forget($key);

        $callback($key);
    }

    protected function events(string $key): bool
    {
        if (! $this->adapter->exists($key)) {
            return false;
        }

        if ($this->onExistsLeave) {
            return true;
        }

        if ($this->onExistsDelete) {
            $this->adapter->forget($key);
        }

        return true;
    }
}

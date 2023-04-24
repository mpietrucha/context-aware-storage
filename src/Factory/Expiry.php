<?php

namespace Mpietrucha\Storage\Factory;

use DateTime;
use Closure;
use Carbon\Carbon;
use Mpietrucha\Support\Types;
use Mpietrucha\Storage\Contracts\ExpiryInterface;
use Mpietrucha\Storage\Adapter;

abstract class Expiry implements ExpiryInterface
{
    abstract protected function adapter(): Adapter;

    public function expiry(string $key, mixed $expires): void
    {
        if (! $expires) {
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

        $callback($key);
    }
}

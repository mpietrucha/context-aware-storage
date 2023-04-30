<?php

namespace Mpietrucha\Storage\Factory;

use Closure;
use Exception;
use Carbon\Carbon;
use DateTimeInterface;
use Mpietrucha\Support\Types;
use Mpietrucha\Storage\Adapter;
use Mpietrucha\Storage\Concerns\HasTable;
use Mpietrucha\Storage\Contracts\ExpiryInterface;

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

        $this->adapter()->put($key, $this->resolve($expires)->getTimestamp());
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

    public function onExpiresResolved(Closure $callback): void
    {
        $this->onExpiresResolved = $callback;
    }

    protected function resolve(mixed $expires): Carbon
    {
        if (Types::int($expires) || Types::string($expires)) {
            return $this->resolve([$expires, 'minutes']);
        }

        if (Types::array($expires)) {
            return $this->resolve(Carbon::now()->add(...$expires));
        }

        if ($expires instanceof DateTimeInterface && ! $expires instanceof Carbon) {
            return $this->resolve(new Carbon($expires));
        }

        if (! $expires instanceof Carbon) {
            throw new Exception('Expected expires values are array[int, duration], int[minutes] or DateTimeInterface object');
        }

        return value($this->onExpiresResolved, $expires) ?? $expires;
    }
}

<?php

namespace Mpietrucha\Storage\Resolver;

use Closure;
use Exception;
use Carbon\Carbon;
use DateTimeInterface;
use Mpietrucha\Support\Types;
use Mpietrucha\Support\Rescue;
use Mpietrucha\Support\Concerns\HasFactory;

class ExpiryDateResolver
{
    use HasFactory;

    protected const DEFAULT_INDICATOR = 'minutes';

    public function __construct(protected mixed $expiry)
    {
    }

    public function encode(?Closure $resolver = null): int
    {
        $date = $this->resolve();

        return (value($resolver, $date) ?? $date)->getTimestamp();
    }

    public function expired(): bool
    {
        return Rescue::create(
            fn () => Carbon::createFromTimestamp($this->expiry)->isAfter(Carbon::now())
        )->call(false);
    }

    protected function resolve(): Carbon
    {
        if (Types::array($this->expires)) {
            return $this->resolveFromArray($resolver);
        }

        if ($this->validDuration($this->expires)) {
            return $this->resolve([$this->expires, self::DEFAULT_INDICATOR]);
        }

        if ($expires = $this->resolveFromDateTimeInterface($this->expires)) {
            return $expires;
        }

        throw new Exception('Expected expires values are array[duration, indicator], int|string[minutes], Carbon, or DateTimeInterface object');
    }

    protected function resolveFromDateTimeInterface(mixed $expires): ?Carbon
    {
        if (! $expires instanceof DateTimeInterface) {
            return null;
        }

        if ($expires instanceof Carbon) {
            return $expires;
        }

        return $this->resolveFromDateTimeInterface(new Carbon($expires));
    }

    protected function resolveFromArray(array $expires): Carbon
    {
        [$duration, $indicator] = $expires;

        if (! $this->validDuration($duration)) {
            throw new Exception('Duration must be of type string or int');
        }

        if (! Types::string($indicator)) {
            throw new Exception('Duration indictor must be of type string');
        }

        return Carbon::now()->add($duration, $indictor);
    }

    protected function validDuration(mixed $duration): bool
    {
        return Types::int($duration) || Types::string($duration);
    }
}

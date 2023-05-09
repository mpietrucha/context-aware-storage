<?php

namespace Mpietrucha\Storage\Expiry;

use Exception;
use Closure;
use Carbon\Carbon;
use DateTimeInterface;
use Mpietrucha\Support\Types;
use Mpietrucha\Support\Rescue;
use Mpietrucha\Support\Condition;
use Mpietrucha\Support\Concerns\Sleepable;
use Mpietrucha\Storage\Contracts\ExpiryDateResolverInterface;

class CarbonDateResolver implements ExpiryDateResolverInterface
{
    use Sleepable;

    protected string $indicator;

    protected const DEFAULT_INDICATOR = 'minutes';

    public function __construct(protected mixed $expires)
    {
        $this->setIndicator(self::DEFAULT_INDICATOR);
    }

    public function setIndicator(string $indicator): self
    {
        $this->indicator = $indicator;

        return $this;
    }

    public function encode(?Closure $resolver = null): int
    {
        return Condition::create($date = $this->resolve())
            ->add(fn () => value($resolver, $date), ! Types::null($resolver))
            ->resolve()
            ->getTimestamp();
    }

    public function expired(): bool
    {
        return Rescue::create(
            fn () => Carbon::createFromTimestamp($this->expires)->isBefore(Carbon::now())
        )->call(false);
    }

    protected function resolve(): Carbon
    {
        if ($this->expires instanceof $this) {
            return $this->expires->resolve();
        }

        if (Types::array($this->expires)) {
            return $this->resolveFromArray($this->expires);
        }

        if ($this->validDuration($this->expires) && $indicator = $this->indicator) {
            return $this->resolve([$this->expires, $indicator]);
        }

        if ($expires = $this->resolveFromDateTimeInterface($this->expires)) {
            return $expires;
        }

        throw new Exception("Expected expires values are array[duration, indicator], int|string[$indicator], Carbon, or DateTimeInterface or self instance");
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

        return Carbon::now()->add(
            $this->sleep($duration, $indicator)->duration()
        );
    }

    protected function resolveFromDateTimeInterface(): ?Carbon
    {
        if (! $this->expires instanceof DateTimeInterface) {
            return null;
        }

        return Carbon::createFromTimestamp($this->expires->getTimestamp());
    }

    protected function validDuration(mixed $duration): bool
    {
        return Types::int($duration) || Types::string($duration);
    }
}

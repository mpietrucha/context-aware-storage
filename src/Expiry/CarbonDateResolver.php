<?php

namespace Mpietrucha\Storage\Expiry;

use Exception;
use Closure;
use Mpietrucha\Support\Types;
use Mpietrucha\Support\Rescue;
use Mpietrucha\Support\Condition;
use Mpietrucha\Storage\Contracts\ExpiryDateResolverInterface;

class CarbonDateResolver implements ExpiryDateResolverInterface
{
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
        dd($this->expiry);
        return Rescue::create(
            fn () => Carbon::createFromTimestamp($this->expiry)->isAfter(Carbon::now())
        )->call(false);
    }

    protected function resolve(): Carbon
    {
        if ($this->expires instanceof $this) {
            return $this->expires->resolve();
        }

        if (Types::array($this->expires)) {
            return $this->resolveFromArray($resolver);
        }

        if ($this->validDuration($this->expires)) {
            return $this->resolve([$this->expires, self::DEFAULT_INDICATOR]);
        }

        if ($expires = $this->resolveFromDateTimeInterface($this->expires)) {
            return $expires;
        }

        throw new Exception('Expected expires values are array[duration, indicator], int|string[minutes], Carbon, or DateTimeInterface or self instance');
    }
}

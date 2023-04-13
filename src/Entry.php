<?php

namespace Mpietrucha\Storage;

use Closure;
use Mpietrucha\Php\Error\Reporting;
use Mpietrucha\Support\Concerns\HasFactory;

use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;

class Entry
{
    use HasFactory;

    public function __construct(protected mixed $value)
    {
    }

    public function resolve(): mixed
    {
        return $this->handler(fn () => unserialize($this->value));
    }

    public function value(): string
    {
        return $this->handler(fn () => serialize($this->value));
    }

    protected function handler(Closure $callback): mixed
    {
        return Reporting::create('8.*')->withoutDeprecated()->while($callback);
    }
}

<?php

namespace Mpietrucha\Storage\Factory;

use Illuminate\Support\Collection;
use Mpietrucha\Storage\Contracts\TransformerInterface;

abstract class Transformer implements TransformerInterface
{
    abstract protected function build(?string $table, ?string $key): ?Collection;

    public function transform(?string $table, ?string $key): ?string
    {
        return $this->build($table, $key)?->toDotWord();
    }

    public function is(?string $table, ?string $key): bool
    {
        return $this->build($table, $key)?->first() === $table;
    }
}

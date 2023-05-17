<?php

namespace Mpietrucha\Storage\Transformer;

use Illuminate\Support\Collection;
use Mpietrucha\Storage\Concerns\HasTable;
use Mpietrucha\Storage\Contracts\TransformerInterface;

abstract class AbstractTransformer implements TransformerInterface
{
    use HasTable;

    abstract protected function build(?string $key): ?Collection;

    public function transform(?string $key): ?string
    {
        return $this->build($key)?->toDotWord();
    }

    public function is(?string $key): bool
    {
        return $this->build($key)?->first() === $this->table;
    }
}

<?php

namespace Mpietrucha\Storage\Transformer;

use Mpietrucha\Support\Hash;
use Mpietrucha\Support\Types;
use Illuminate\Support\Collection;
use Mpietrucha\Storage\Contracts\TransformerInterface;

class Transformer implements TransformerInterface
{
    protected ?string $table = null;

    public function table(string $table): void
    {
        $this->table = $table;
    }

    public function shouldTransform(): bool
    {
        return ! Types::null($this->table);
    }

    public function inside(?string $key): bool
    {
        return $this->build($key)->first() === $this->table;
    }

    public function transform(?string $key): ?string
    {
        if (! $this->shouldTransform()) {
            return $key;
        }

        return $this->build($key)->toDotWord();
    }

    protected function build(?string $key): Collection
    {
        return collect([
            Hash::md5($this->table), $key
        ]);
    }
}

<?php

namespace Mpietrucha\Storage;

use Closure;
use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Support\Concerns\ForwardsCalls;
use Mpietrucha\Storage\Contracts\AdapterInterface;
use Mpietrucha\Storage\Adapter\File;
use Mpietrucha\Support\Caller;
use Mpietrucha\Support\Collection;
use Mpietrucha\Support\Hash;
use Mpietrucha\Support\Vendor;

class Adapter
{
    use HasFactory;

    use ForwardsCalls;

    protected ?string $table = null;

    protected ?Closure $builder = null;

    public function __construct(protected AdapterInterface $adapter = new File)
    {
        $this->forwardTo(new Processor($adapter));

        $this->forwardsArgumentsTransformer(function (Collection $arguments) {
            $arguments->get(0)->nullable()->string()->transform(
                Caller::create($this->builder)->add($this->defaultBuildStrategy(...))->get()
            );
        });
    }

    public function table(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function vendor(): self
    {
        return $this->table(Vendor::create());
    }

    public function setBuildStrategy(Closure $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    public function adapter(?Closure $callback = null): AdapterInterface
    {
        return value($callback, $this->adapter) ?? $this->adapter;
    }

    protected function defaultBuildStrategy(?string $key): ?string
    {
        if (! $this->table || ! $key) {
            return $key;
        }

        return collect([
            Hash::md5($this->table), $key
        ])->toDotWord();
    }
}

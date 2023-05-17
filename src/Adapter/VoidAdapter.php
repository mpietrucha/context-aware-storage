<?php

namespace Mpietrucha\Storage\Adapter;

use Exception;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Mpietrucha\Storage\Contracts\AdapterInterface;
use Mpietrucha\Storage\Contracts\ProcessorInterface;

class VoidAdapter extends Adapter
{
    protected ?Enumerable $storage = null;

    public function __construct(protected AdapterInterface $adapter)
    {
    }

    public function table(?string $table): ?string
    {
        return $table;
    }

    public function processor(): ProcessorInterface
    {
        $processor = $this->adapter->processor()::class;

        return new $processor($this);
    }

    public function delete(): void
    {
        throw new Exception('Delete is not supported in VoidAdapter');
    }

    public function get(): Enumerable
    {
        return $this->storage ??= $this->adapter->get();
    }

    public function set(Enumerable $storage): void
    {
        $this->storage = $storage;
    }
}

<?php

namespace Mpietrucha\Storage;

use Closure;
use Mpietrucha\Support\Collection;
use Mpietrucha\Storage\Adapter\File;
use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Support\Concerns\ForwardsCalls;
use Mpietrucha\Storage\Transformer\Transformer;
use Mpietrucha\Storage\Contracts\AdapterInterface;
use Mpietrucha\Storage\Contracts\TransformerInterface;

class Adapter
{
    use HasFactory;

    use ForwardsCalls;

    public function __construct(protected AdapterInterface $adapter = new File, protected TransformerInterface $transformer = new Transformer)
    {
        $this->forwardTo(fn () => $adapter->processor())->forwardsArgumentsTransformer(function (Collection $arguments) {
            $arguments->get(0)->nullable()->string()->transform($this->transformer->transform(...));
        });
    }

    public function adapter(?Closure $callback = null): AdapterInterface
    {
        return value($callback, $this->adapter) ?? $this->adapter;
    }

    public function table(string $table): self
    {
        $this->transformer->table($table);

        return $this;
    }

    public function delete(): void
    {
        if (! $this->transformer->shouldTransform()) {
            $this->adapter->delete();

            return;
        }

        $storage = $this->adapter->get()->filter(function (mixed $value, string $key) {
            return $this->transformer->inside($key);
        });

        $this->adapter->set($storage);
    }
}

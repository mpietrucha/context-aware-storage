<?php

namespace Mpietrucha\Storage;

use Closure;
use Mpietrucha\Support\Collection;
use Mpietrucha\Storage\Expiry\FileExpiry;
use Mpietrucha\Storage\Adapter\FileAdapter;
use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Support\Concerns\ForwardsCalls;
use Mpietrucha\Storage\Contracts\ExpiryInterface;
use Mpietrucha\Storage\Contracts\AdapterInterface;
use Mpietrucha\Storage\Contracts\TransformerInterface;
use Mpietrucha\Storage\Transformer\HashTableDotNotationTransformer;

class Adapter
{
    use HasFactory;

    use ForwardsCalls;

    protected ?string $table = null;

    public function __construct(
        protected AdapterInterface $adapter = new FileAdapter,
        protected TransformerInterface $transformer = new HashTableDotNotationTransformer,
        protected ?ExpiryInterface $expiry = new FileExpiry
    ) {
        $this->forwardTo(fn () => $adapter->processor());

        $this->forwardsArgumentsTransformer(function (Collection $arguments) {
            $arguments->get(0)->nullable()->string()->transform(fn (?string $key) => $this->transformer->transform($this->table, $key));
        });

        $this->forwardsMethodTap(['get', 'raw'], function (?string $key = null) {
            $this->expiry?->expired($key, $this->forwardsTo->forget(...));
        });

        $this->forwardsMethodTap('put', function (string $key, mixed $value, mixed $expires = null) {
            $this->expiry?->expiry($key, $expires);
        });
    }

    public function adapter(Closure|AdapterInterface $adapter): self
    {
        $this->adapter = value($adapter, $this->adapter);

        return $this;
    }

    public function transformer(TransformerInterface $transformer): self
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function expiry(?ExpiryInterface $expiry): self
    {
        $this->expiry = $expiry;

        return $this;
    }

    public function table(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function delete(): void
    {
        if (! $this->table) {
            $this->adapter->delete();

            return;
        }

        $storage = $this->adapter->get()->filter(function (mixed $value, string $key) {
            return $this->transformer->is($this->table, $key);
        });

        $this->adapter->set($storage);
    }
}

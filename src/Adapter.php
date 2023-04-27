<?php

namespace Mpietrucha\Storage;

use Closure;
use Mpietrucha\Support\Condition;
use Mpietrucha\Support\Collection;
use Mpietrucha\Storage\Expiry\FileExpiry;
use Mpietrucha\Storage\Concerns\HasTable;
use Mpietrucha\Storage\Adapter\FileAdapter;
use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Storage\Contracts\AdapterInterface;
use Mpietrucha\Storage\Contracts\TransformerInterface;
use Mpietrucha\Storage\Contracts\ExpiryInterface;
use Mpietrucha\Support\Concerns\ForwardsCalls;
use Mpietrucha\Storage\Transformer\DefaultTransformer;

class Adapter
{
    use HasFactory;

    use ForwardsCalls;

    use HasTable {
        table as withTable;
    }

    protected ?self $transaction = null;

    protected const ADAPTER_SETTERS  = ['put'];

    protected const ADAPTER_GETTERS = ['get', 'raw'];

    public function __construct(
        protected AdapterInterface $adapter = new FileAdapter,
        protected TransformerInterface $transformer = new DefaultTransformer,
        protected ?ExpiryInterface $expiry = new FileExpiry
    )
    {
        $this->forwardTo(fn () => $adapter->processor());

        $this->forwardArgumentsTransformer(function (Collection $arguments) {
            $arguments->get(0)->nullable()->string()->transform($this->transformer->transform(...));
        });

        $this->forwardMethodTap(self::ADAPTER_GETTERS, function (?string $key = null) {
            $this->expiry?->expired($key, $this->forwardTo->forget(...));
        });

        $this->forwardMethodTap(self::ADAPTER_SETTERS, function (string $key, mixed $value, mixed $expires = null) {
            $this->expiry?->expiry($key, $expires);
        });
    }

    public function adapter(Closure|AdapterInterface $adapter): self
    {
        $this->adapter = value($adapter, $this->adapter) ?? $this->adapter;

        return $this;
    }

    public function transformer(Closure|TransformerInterface $transformer): self
    {
        $this->transformer = value($transformer, $this->transformer) ?? $this->adapter;

        return $this;
    }

    public function expiry(null|Closure|ExpiryInterface $expiry): self
    {
        $this->expiry = Condition::create(function () use ($expiry) {
            return value($expiry, $this->expiry) ?? $this->expiry;
        })->addNull(! $expiry)->resolve();

        return $this;
    }

    public function stale(): self
    {
        return $this->expiry(null);
    }

    public function table(?string $adapter, ?string $transformer = null, ?string $expiry = null): self
    {
        $this->expiry?->table($expiry ?? $adapter ?? $transformer);

        $adapter = $this->adapter->table($adapter);

        $this->withTable($transformer ?? $adapter);

        $this->transformer->table($this->table);

        return $this;
    }

    public function delete(): void
    {
        if (! $this->table) {
            $this->adapter->delete();

            return;
        }

        $storage = $this->adapter->get()->filter(function (mixed $value, string $key) {
            return $this->transformer->is($key);
        });

        $this->adapter->set($storage);
    }

    public function transaction(?Closure $callback = null): Transaction|self
    {
        $transaction = Transaction::create($this->adapter, $this->table)->willReturnTo(fn () => $this);

        if ($callback) {
            value($callback->bindTo($transaction));

            return $transaction->commit();
        }

        return $transaction;
    }
}

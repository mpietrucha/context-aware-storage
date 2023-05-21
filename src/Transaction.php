<?php

namespace Mpietrucha\Storage;

use Closure;
use Mpietrucha\Exception\RuntimeException;
use Mpietrucha\Storage\Adapter;
use Mpietrucha\Storage\Adapter\VoidAdapter;
use Mpietrucha\Support\Concerns\HasReturn;
use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Support\Concerns\ForwardsCalls;
use Mpietrucha\Storage\Contracts\AdapterInterface;

class Transaction
{
    use HasReturn;

    use HasFactory;

    use ForwardsCalls;

    public function __construct(protected AdapterInterface $adapter, array $expiryTapperBuilder, ?string $table)
    {
        if ($adapter instanceof VoidAdapter) {

            throw new RuntimeException('Cannot create transaction with', [VoidAdapter::class]);
        }

        $this->forwardTo(
            Adapter::create(VoidAdapter::create($adapter))->stale()->table($table)
        )->forwardMethodTap(...$expiryTapperBuilder);
    }

    public function commit(): mixed
    {
        $this->adapter->set($this->getForward()->raw());

        return $this->makeReturn();
    }
}

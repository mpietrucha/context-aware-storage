<?php

namespace Mpietrucha\Storage;

use Closure;
use Exception;
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

    public function __construct(protected AdapterInterface $adapter, ?string $table)
    {
        if ($adapter instanceof VoidAdapter) {
            throw new Exception('Cannot create transaction with VoidAdapter');
        }

        $this->forwardTo(
            Adapter::create(VoidAdapter::create($adapter))->stale()->table($table)
        );
    }

    public function commit(): mixed
    {
        $this->adapter->set($this->forwardsTo->raw());

        return $this->makeReturn();
    }
}

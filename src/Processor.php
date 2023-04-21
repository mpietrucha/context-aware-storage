<?php

namespace Mpietrucha\Storage;

use Exception;
use Closure;
use Mpietrucha\Support\Serializer;
use Mpietrucha\Support\Caller;
use Illuminate\Support\Collection;
use Mpietrucha\Storage\Contracts\AdapterInterface;

class Processor
{
    public function __construct(protected AdapterInterface $adapter)
    {
    }

    public function raw(string $key = null): null|string|Collection
    {
        return $this->get($key, fn (string $entry) => $entry);
    }

    public function serializer(?string $key): null|Serializer|Collection
    {
        return $this->get($key, fn (string $entry) => Serializer::create($entry));
    }

    public function get(?string $key = null, ?Closure $map = null): mixed
    {
        $entry = $this->adapter->get()->when($key, fn (Collection $storage) => $storage->get($key));

        $callback = Caller::create($map)->add(fn (string $entry) => Serializer::create($entry)->unserialize());

        if ($entry instanceof Collection) {
            return $entry->mapRecursive($callback->get());
        }

        return $callback->call($entry);
    }

    public function put(string $key, mixed $value): void
    {
        $storage = $this->adapter->get()->put($key, Serializer::create($value)->serialize());

        $this->adapter->set($storage);
    }

    public function append(string $key, mixed $value): void
    {
        $current = $this->enshureCollection($key, true);

        $this->put($key, $current->push($value));
    }

    public function appendUnique(string $key, mixed $value, Closure $callback): void
    {
        if ($this->existsUnique($key, $callback, $value)) {
            return;
        }

        $this->append($key, $value);
    }

    public function exists(string $key): bool
    {
        return $this->adapter->get()->has($key);
    }

    public function existsUnique(string $key, Closure $callback, mixed $value = null): bool
    {
        if (! $this->exists($key)) {
            return false;
        }

        $current = $this->enshureCollection($key, true);

        $this->put($temporaryKey = uniqid(), $value);

        $exists = $current->first(fn (mixed $entry) => $callback($entry, $temporaryValueEntry ??= $this->get($temporaryKey))) !== null;

        $this->forget($temporaryKey);

        return $exists;
    }

    public function forget(string $key): void
    {
        $storage = $this->adapter->get()->forget($key);

        $this->adapter->set($storage);

        if ($this->adapter->get()->count()) {
            return;
        }

        $this->delete();
    }

    public function forgetIndex(string $key, int $index): void
    {
        $current = $this->enshureCollection($key);

        $current->splice($index, 1);

        $this->put($key, $current);

        if ($this->get($key)->count()) {
            return;
        }

        $this->forget($key);
    }

    public function delete(): void
    {
        $this->adapter->delete();
    }

    protected function enshureCollection(string $key, bool $default = false): Collection
    {
        $current = $this->get($key);

        if ($default) {
            $current ??= collect();
        }

        if (! $current instanceof Collection) {
            throw new Exception("Cannot append to key `$key` with previously non array value.");
        }

        return $current;
    }
}

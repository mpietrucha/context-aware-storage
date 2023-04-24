<?php

namespace Mpietrucha\Storage\Factory;

use Closure;
use Exception;
use Illuminate\Support\Collection;
use Mpietrucha\Storage\Contracts\ProcessorInterface;

abstract class Processor implements ProcessorInterface
{
    public function append(string $key, mixed $value, mixed $expires = null): void
    {
        $current = $this->enshureCollection($key, true);

        $this->put($key, $current->push($value), $expires);
    }

    public function appendUnique(string $key, mixed $value, Closure $callback, mixed $expires = null): void
    {
        if ($this->existsUnique($key, $callback, $value)) {
            return;
        }

        $this->append($key, $value, $expires);
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

        $this->put($temporaryKey = str()->uuid(), $value);

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

        $this->adapter->delete();
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

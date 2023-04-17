<?php

namespace Mpietrucha\Storage;

use Closure;
use Exception;
use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Storage\Concerns\AdapterInterface;
use Mpietrucha\Storage\Adapter\File;
use Illuminate\Support\Collection;
use Mpietrucha\Support\Condition;
use Mpietrucha\Support\Hash;

class Adapter
{
    use HasFactory;

    protected Closure $builder;

    protected ?string $table = null;

    public function __construct(protected AdapterInterface $adapter = new File)
    {
        $this->setBuildStrategy($this->getDefaultBuildStrategy(...));
    }

    public function table(string $table): self
    {
        $this->table = $table;

        return $this;
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

    public function get(?string $key = null): mixed
    {
        $storage = $this->adapter->get()->mapRecursive(fn (string $entry) => Entry::create($entry)->resolve());

        if ($key) {
            $storage = $storage->get($this->build($key));
        }

        return Condition::create($storage)->add(fn () => $storage->recursive(), $storage instanceof Collection)->resolve();
    }

    public function put(string $key, mixed $value): void
    {
        $storage = $this->adapter->get()->put($this->build($key), Entry::create($value)->value());

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

    public function forget(string $key): void
    {
        $storage = $this->adapter->get()->forget($this->build($key));

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

    public function exists(string $key): bool
    {
        return $this->adapter->get()->has($this->build($key));
    }

    public function existsUnique(string $key, Closure $callback, mixed $value = null): bool
    {
        if (! $this->exists($key)) {
            return false;
        }

        $current = $this->enshureCollection($key, true);

        $compareValueEntry = Entry::create($value)->loop();

        return $current->first(fn (mixed $entry) => $callback($entry, $compareValueEntry)) !== null;
    }

    public function delete(): void
    {
        $this->adapter->delete();
    }

    protected function getDefaultBuildStrategy(string $key): string
    {
        if (! $this->table) {
            return $key;
        }

        return collect([
            Hash::md5($this->table), $key
        ])->toDotWord();
    }

    protected function build(string $key): string
    {
        return ($this->builder)($key);
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

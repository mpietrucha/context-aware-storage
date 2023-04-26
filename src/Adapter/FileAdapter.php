<?php

namespace Mpietrucha\Storage\Adapter;

use Mpietrucha\Support\Macro;
use Mpietrucha\Support\Hash;
use Mpietrucha\Support\File;
use Illuminate\Support\Enumerable;
use Mpietrucha\Storage\Factory\Adapter;
use Mpietrucha\Storage\Processor\SerializableProcessor;
use Mpietrucha\Storage\Contracts\ProcessorInterface;

class FileAdapter extends Adapter
{
    protected string $file;

    protected string $directory;

    protected const DEFAULT_SHARED_FILE = 'context_aware_storage_default';

    public function __construct()
    {
        Macro::bootstrap();

        $this->directory(sys_get_temp_dir())->file(self::DEFAULT_SHARED_FILE);
    }

    public function directory(string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function file(string ...$files): self
    {
        $this->file = Hash::md5(...$files);

        return $this;
    }

    public function table(?string $table): ?string
    {
        if ($table) {
            $this->file($this->file, $table);
        }

        return null;
    }

    public function processor(): ProcessorInterface
    {
        return new SerializableProcessor($this);
    }

    public function delete(): void
    {
        File::delete($this->path());
    }

    public function get(): Enumerable
    {
        return File::lazyCollection($this->path());
    }

    public function set(Enumerable $storage): void
    {
        File::putLazyCollection($this->path(), $storage);
    }

    protected function path(): string
    {
        return collect([$this->directory, $this->file])->toDirectory();
    }
}

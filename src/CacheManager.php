<?php

namespace Facecode\CacheManager;

/**
 * Class CacheManager
 * Temporary key-value storage for data cashing on disc
 * @package Facecode/CacheManager
 */
class CacheManager
{
    /**
     * @var bool|resource
     */
    protected $filePointer;

    /**
     * @var int|null
     */
    protected $size;

    /**
     * CacheManager constructor.
     * @param int $bufferSize In-memory cashe buffer size in megabytes. Data above this size temporary stored on disk.
     */
    public function __construct($bufferSize = 10)
    {
        $bufferSize = $bufferSize * 1024 * 1024;
        $this->filePointer = fopen("php://temp/maxmemory:$bufferSize", 'w+');
    }

    public function __destruct()
    {
        fclose($this->filePointer);
    }

    protected function readStorage(): array
    {
        fseek($this->filePointer, 0);
        $str = fgets($this->filePointer, $this->size);

        return empty($str) ? [] : unserialize($str);
    }

    protected function writeStorage(array $storage): bool
    {
        $str = serialize($storage);

        fseek($this->filePointer, 0);
        if (!$result = fputs($this->filePointer, $str)) {
            return false;
        }
        $this->size = $result + 1;
        return true;
    }

    public function get($key)
    {
        $storage = $this->readStorage();

        return empty($storage[$key]) ? null : $storage[$key];
    }

    public function store($key, $value)
    {
        $storage = $this->readStorage();
        $storage[$key] = $value;
        return $this->writeStorage($storage);
    }

    public function delete($key): bool
    {
        $storage = $this->readStorage();
        unset($storage[$key]);
        return $this->writeStorage($storage);
    }

    public function has($key)
    {
        $storage = $this->readStorage();
        return isset($storage[$key]);
    }
}
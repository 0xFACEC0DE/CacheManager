<?php

namespace Facecode/CacheManager;

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
    private $filePointer;

    /**
     * @var int|null
     */
    private $size;

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

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        fseek($this->filePointer, 0);
        $str = fgets($this->filePointer, $this->size);
        $storage = unserialize($str);

        if (empty($storage[$key])) return false;
        return $storage[$key];
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function store($key, $value)
    {
        $storage = [];
        if ($this->size) {
            fseek($this->filePointer, 0);
            $str = fgets($this->filePointer, $this->size);
            $storage = unserialize($str);
            if (!$storage) return false;
        }

        $storage[$key] = $value;
        $str = serialize($storage);
        fseek($this->filePointer, 0);
        $result = fputs($this->filePointer, $str);

        if (!$result) return false;
        $this->size = $result + 1;
        return true;
    }
}
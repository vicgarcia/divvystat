<?php
namespace DivvyStat;

class Cache
{
    public static
        $host = null,
        $port = null,
        $database = 0;

    protected
        $prefix = 'divvystat:',
        $redis;

    public function __construct()
    {
        $redis = new \Redis;
        $redis->connect(self::$host, self::$port);
        $redis->select(self::$database);
        $this->redis = $redis;
    }

    public function load($key)
    {
        $cached = $this->redis->get($this->formatKey($key));
        return (empty($cached) or ($cached === false)) ? false : unserialize($cached);
    }

    public function save($key, $value, $ttl = null)
    {
        if (is_int($ttl) and ($ttl != 0)) {
            return $this->redis->setex($this->formatKey($key), $ttl, serialize($value));
        } else {
            return $this->redis->set($this->formatKey($key), serialize($value));
        }
    }

    public function delete($key)
    {
        return $this->redis->delete($this->formatKey($key));
    }

    public function clear()
    {
        $deleted = 0;
        foreach ($this->redis->keys($this->prefix . '*') as $key) {
            $this->redis->del($key);
            $deleted++;
        }
        return $deleted;
    }

    public function formatKey($key)
    {
        return $this->prefix . $key;
    }
}

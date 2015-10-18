<?php

namespace rockunit\common;


use rock\cache\versioning\Memcached;
use rock\helpers\FileHelper;

trait CommonTestTrait
{
    protected static function clearRuntime()
    {
        $runtime = ROCKUNIT_RUNTIME;
        FileHelper::deleteDirectory($runtime);
    }

    protected static function sort($value)
    {
        ksort($value);
        return $value;
    }

    /**
     * @param array $config
     * @return \rock\cache\CacheInterface
     */
    protected static function getCache(array $config = [])
    {
        return new Memcached($config);
    }
} 
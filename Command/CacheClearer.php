<?php

namespace noFlash\SupercacheBundle\Command;


use noFlash\SupercacheBundle\Cache\CacheManager;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

/**
 * Listen for global cache clear event and clears supercache caches.
 *
 * @see {http://symfony.com/doc/current/reference/dic_tags.html#kernel-cache-clearer}
 */
class CacheClearer implements CacheClearerInterface
{
    /**
     * @var CacheManager
     */
    private $manager;

    /**
     * @param CacheManager $manager
     */
    public function __construct(CacheManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Clears any caches necessary.
     *
     * @param string $cacheDir The cache directory - it's unused because SupercacheBundle uses different directory
     *     outside standard caching directory.
     */
    public function clear($cacheDir)
    {
        $this->manager->clear();
    }
}
